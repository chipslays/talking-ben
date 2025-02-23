<?php

namespace App\Controllers;

use Alisa\Context;
use Alisa\Support\Markup;
use Alisa\Yandex\Sessions\Session;
use App\Models\User;
use Throwable;

class MainController
{
    public function beforeRun(Context $context)
    {
        if ($context->get('session.new') && !Session::get('already_call')) {
            Session::set('already_call', true);
            user()->increment('call_count');
        }
    }

    public function start(Context $context)
    {
        $text = Markup::variant([
            'Выполняется звонок Б{{е},{+э}}ну...',
            'Звоню Б{{е},{+э}}ну, секунду...',
            'Звоню Б{{е},{+э}}ну, одну минуту...',
            'Минуточку, набираю Б{{е},{+э}}на...',
        ]);

        $context->reply("
            {pause:550}

            📞 {$text}

            {text:💬 Задавай вопросы на которые  можно ответить <<<да>>> или <<<нет>>>.}

            {pause:550}

            {audio:allo}
        ", buttons: 'main');
    }

    public function help(Context $context)
    {
        $context->reply('
            💬 Сейчас ты разговариваешь с Б{{е},{+э}}ном, задавай ему любые вопросы на которые можно ответить <<<да>>> или <<<нет>>>.

            💬 Чтобы закончить, скажи ему <<<пока>>>.

            💬 А чтобы узнать статистику, скажи <<<статистика>>>.

            📞 Передаю трубку обратно Б{{е},{+э}}ну.

            {audio:ben}
        ', buttons: 'main');
    }

    public function features(Context $context)
    {
        $context->reply('
            💬 Задавай любые вопросы Б{{е},{+э}}ну, а он ответит: <<<да>>> или <<<нет>>>.

            📞 Передаю трубку обратно Б{{е},{+э}}ну.

            {audio:ben}
        ', buttons: 'main');
    }

    public function bye(Context $context)
    {
        $context->finish('💬🐶 ...', '{audio:phone-drop}');
    }

    public function stats(Context $context)
    {
        $userCount = User::count();

        $userQuestionCount = user()->question_count;
        $userCallCount = user()->call_count;

        $totalQuestionCount = User::sum('question_count');
        $totalCallCount = User::sum('call_count');

        if ($totalQuestionCount != 0) {
            $userQuestionPercentage = round(($userQuestionCount / $totalQuestionCount) * 100);
        } else {
            $userQuestionPercentage = 0;
        }

        if ($totalCallCount != 0) {
            $userCallPercentage = round(($userCallCount / $totalCallCount) * 100);
        } else {
            $userCallPercentage = 0;
        }

        $context->reply("
            Вы позвонили Б{{е},{+э}}ну 📞 {{$userCallCount}: раз, раза, раз} и задали 💬 {{$userQuestionCount}: вопрос, вопроса, вопросов}!

            А всего {{$userCount}: пользователь позвонил, пользователя позвонили, пользователей позвонили} Б{{е},{+э}}ну 📞 {{$totalCallCount}: раз, раза, раз} (из них ваши составляют {$userCallPercentage}%) и задали 💬 {{$totalQuestionCount}: вопрос, вопроса, вопросов} (из них ваши составляют {$userQuestionPercentage}%).

            📞 Передаю трубку обратно Б{{е},{+э}}ну.

            {audio:ben}
        ", buttons: 'main');
    }

    public function fallback(Context $context)
    {
        user()->increment('question_count');

        $chances = [
            ['%' => 42.5, 'result' => ['text' => '💬🐶 Yee-es...', 'sound' => 'yes']],
            ['%' => 42.5, 'result' => ['text' => '💬🐶 No.', 'sound' => 'no']],
            ['%' => 10, 'result' => ['text' => '💬🐶 Hoho-ho...', 'sound' => 'hohoho']],
            ['%' => 5, 'result' => ['text' => '💬🐶 Ughh.', 'sound' => 'ughh']]
        ];

        $result = roll($chances);

        $context->reply($result['text'], '{audio:' . $result['sound'] . '}', buttons: 'main');
    }

    public function yesOrNo(Context $context)
    {
        user()->increment('question_count');

        $chances = [
            ['%' => 50, 'result' => ['text' => '💬🐶 Yee-es...', 'sound' => 'yes']],
            ['%' => 50, 'result' => ['text' => '💬🐶 No.', 'sound' => 'no']],
        ];

        $result = roll($chances);

        $context->reply($result['text'], '{audio:' . $result['sound'] . '}', buttons: 'main');
    }

    public function yes(Context $context)
    {
        user()->increment('question_count');

        $chances = [
            ['%' => 90, 'result' => ['text' => '💬🐶 Yee-es...', 'sound' => 'yes']],
            ['%' => 10, 'result' => ['text' => '💬🐶 No.', 'sound' => 'no']],
        ];

        $result = roll($chances);

        $context->reply($result['text'], '{audio:' . $result['sound'] . '}', buttons: 'main');
    }

    public function no(Context $context)
    {
        user()->increment('question_count');

        $chances = [
            ['%' => 10, 'result' => ['text' => '💬🐶 Yee-es...', 'sound' => 'yes']],
            ['%' => 90, 'result' => ['text' => '💬🐶 No.', 'sound' => 'no']],
        ];

        $result = roll($chances);

        $context->reply($result['text'], '{audio:' . $result['sound'] . '}', buttons: 'main');
    }

    public function exception(Context $context, Throwable $exception)
    {
        $dir = storage_path('logs/exceptions/' . date('Y-m-d'));

        if (!file_exists($dir)) {
            mkdir($dir, 0776, true);
        }

        $file = $dir . '/exceptions.log';

        file_put_contents($file, '[' . date('d.m.Y H:i:s') . "] \n[context] -> " . $context . "\n[exception] -> " . $exception . "\n\n", FILE_APPEND);
    }
}