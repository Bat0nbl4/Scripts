<?php

namespace App\Http\Controllers\Hack;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HackActionController extends Controller
{
    private $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!#$%^&*';
    private $skobs = ['(' => ')', '[' => ']', '{' => '}'];

    private function generate_password($passwords_length = ['min' => 1, 'max' => 5]) {
        $password = '';
        for ($j = 0; $j < rand($passwords_length['min'], $passwords_length['max']); $j++) {
            $password .= $this->characters[rand(0, strlen($this->characters) - 1)];
        }
        return $password;
    }

    private function generate_fake_password($passwords_length = ['min' => 1, 'max' => 5]) {
        $password = '';
        switch (rand(1, 3)) {
            case 1:
                for ($j = 0; $j < rand(0, $passwords_length['min'] - 1); $j++) {
                    $password .= $this->characters[rand(0, strlen($this->characters) - 1)];
                }
                break;
            case 2:
                for ($j = 0; $j < rand($passwords_length['min'], $passwords_length['max']); $j++) {
                    $password .= '0';
                }
                break;
            case 3:
                for ($j = 0; $j < rand($passwords_length['min'], $passwords_length['max']); $j++) {
                    $password .= '!#$%^&*'[rand(0, strlen('!#$%^&*') - 1)];
                }
                break;
        }
        return $password;
    }

    private function generate_text($character_count = 100, $passwords_count = ['min' => 1, 'max' => 2], $passwords_length = ['min' => 1, 'max' => 5]) {
        $passwords = [];
        $skob = array_rand($this->skobs);
        $text = '';
        for ($i = 0; $i < $character_count; $i++) {
            if (rand(1, 1000) == 1) {
                $text .= $skob;
                if (rand(1, 2) == 2 and session()->get('hack.difficulty') != 'normal') {
                    $password = $this->generate_fake_password($passwords_length);
                } else {
                    $password = $this->generate_password($passwords_length);
                    array_push($passwords, $password);
                }

                $text .= $password;
                $text .= $this->skobs[$skob];

                if (count($passwords) > $passwords_count['max']) {
                    return ['text' => 'LOL GG WP EZ SOLO BB', 'passwords' => [null]];
                }
            } else {
                if (rand(1, 50) == 1) {
                    if (rand(0, 1) == 1) {
                        $text .= $skob;
                    } else {
                        $text .= $this->skobs[array_rand(array_filter($this->skobs, function($key) use($skob) {
                            return $key != $skob;
                        }, ARRAY_FILTER_USE_KEY))];
                    }
                } else {
                    $text .= $this->characters[rand(0, strlen($this->characters) - 1)];
                }
            }
        }
        $text .= '#'.strlen($text).'^'.count($passwords);
        $text .= Str::random(5);
        return ['text' => $text, 'passwords' => $passwords];
    }

    public function init($difficulty) {

        switch ($difficulty) {
            case 'normal':
                $passwords_count = ['min' => 3, 'max' => 7];
                $passwords_length = ['min' => 8, 'max' => 16];
                $character_count = 4000;
                $try_count = 5;
                $cost = 500;
                break;
            case 'hard':
                $passwords_count = ['min' => 7, 'max' => 12];
                $passwords_length = ['min' => 8, 'max' => 32];
                $character_count = 8000;
                $try_count = 10;
                $cost = 2500;
                break;
            case 'impossible':
                $passwords_count = ['min' => 8, 'max' => 14];
                $passwords_length = ['min' => 8, 'max' => 64];
                $character_count = 10000;
                $try_count = 12;
                $cost = 10000;
                break;
        }
        /*
        $wallet = Wallet::where('id', session()->get('wallet.active'))->first();
        if ($wallet->value < $cost) {
            return redirect()->route('hack.menu')->with('message', 'INSUFFICIENT FUNDS');
        }
        $wallet->value -= $cost;
        $wallet->save();
        */
        $array = $this->generate_text($character_count, $passwords_count, $passwords_length);
        while (count($array['passwords']) <= $passwords_count['min']) {
            $array = $this->generate_text($character_count, $passwords_count, $passwords_length);
        }
        session()->put(
            ['hack' => [
                'difficulty' => $difficulty,
                'text' => $array['text'],
                'passwords' => $array['passwords'],
                'true_password' => array_rand($array['passwords']),
                'try_count' => $try_count,
                ]
            ]
        );
        return redirect(route('hack.hack'));
    }

    public function try_password(Request $request) {
        if ($request->password == session()->get('hack.passwords')[session()->get('hack.true_password')]) {

            switch (session()->get('hack.difficulty')) {
                case 'normal':
                    $deposit = 500;
                    $prize = rand(100, 1500);
                    break;
                case 'hard':
                    $deposit = 2500;
                    $prize = rand(1000, 5000);
                    break;
                case 'impossible':
                    $deposit = 10000;
                    $prize = rand(5000, 15000);
                    break;
            }

            $wallet = Wallet::where('id', session()->get('wallet.active'))->first();
            $wallet->value += $prize + $deposit;
            $wallet->save();
            session()->put(['hack' => null]);
            return redirect()->route('hack.menu')->with('message', 'YOU HAVE RECEIVED '.$prize.' + '.$deposit);
        } else {
            session()->put('hack.try_count', session()->get('hack.try_count') - 1);
            if (session()->get('hack.try_count') <= 0) {
                session()->put(['hack' => null]);
                return redirect()->route('user.lk')->with('message', 'THE ATTEMPTS ARE OVER');
            }
            return redirect()->back()->with('message', 'INVALID PASSWORD');
        }
    }
}
