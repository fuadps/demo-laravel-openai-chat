<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use OpenAI;

class ChatQuestionCommand extends Command
{

    protected $signature = 'chat:ai';
    protected $description = 'Ask web application to chat with you';
    protected string $defaultInstruction = 'Please get the latest order';

    public function handle(): int
    {
        $question = $this->ask('What is your question?', $this->defaultInstruction);
        $openAI = OpenAI::client(config('openai.key'));

        $response = $openAI
            ->completions()
            ->create($this->instruct($question));

        $query = str($response->choices[0]->text)
            ->trim()
            ->value();

        dump($query);

        $result = null;
        try {
            eval($query); # dont judge 😜
        } catch (Exception $e) {
            $this->comment($e->getMessage());
            $this->error('Cant query the results. Try again with more specific and understandable question.');
            return 1;
        }

        if($result === null) {
            $this->info('Result not found');
            return 1;
        }

        if(is_string($result)) {
            $this->info($result);
            return 0;
        }

        $response = $openAI
            ->completions()
            ->create($this->prompt($this->baseAnswerInstruction($result, $question)));

        $this->warn($response->choices[0]->text);
        return 0;
    }

    private function prompt(string $text): array
    {
        return array_merge(config('openai.options'), [
            'prompt' => $text,
        ]);
    }

    private function instruct(string $instruction): array
    {
        return $this->prompt($this->baseInstruction($instruction));
    }

    private function baseInstruction(string $text): string
    {
        return '
            Imagine this is a Laravel application.
            You are a developer and you have to write a query to get the result based on user question.

            Models - User, Order
            User column - name, email, created_at
            Order column - item, user_id, price, quantity, created_at
            User has a many relationship with order.

            The model class in App\Models namespace.
            Use class name with namespace.
            Dont select column that sensitive to user privacy such as id, password, etc.
            If joining table, use join instead of with().
            When use select(), use table prefix for each column.

            The question must follow this rules:
            1. Action related with create, update or delete are not allowed.
            2. The question must be understandable by human.

            Based on the provided information,
            return laravel eloquent result as a $result based on this question :
            "'.$text.'".

            If the question doesnt follow this rules,
            return an answer with $result as a variable using this text:
            "Your question doesnt make any sense. Try again.".
        ';
    }

    public function baseAnswerInstruction($result, $question): string
    {
        return '
            Based on this json,
            give an answer that can be understandable by human to this question and dont use developer terms.
            If it have a date, please use format that can be understandable by human"'. $question .'".
            Json : '. json_encode($result)
        ;
    }
}
