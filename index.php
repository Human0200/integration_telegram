<?php

require 'vendor/autoload.php';

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;

// Настройки
$settings = new Settings;
$appInfo = new AppInfo;
$appInfo->setApiId(23834746); // Получить свой на my.telegram.org
$appInfo->setApiHash('7fb4d98fd1f39e2f2259b329d286a8e6');
$settings->setAppInfo($appInfo);

echo "🚀 Запуск MadelineProto...\n";
echo "📱 При первом запуске отсканируйте QR-код\n\n";

// Создание клиента
$MadelineProto = new API('session.madeline', $settings);
$MadelineProto->start();

// Получаем информацию о себе
$me = $MadelineProto->getSelf();
echo "\n✅ Успешно подключён!\n";
echo "👤 Имя: " . $me['first_name'] . " " . ($me['last_name'] ?? '') . "\n";
echo "🆔 ID: " . $me['id'] . "\n";
echo "👁 Username: @" . ($me['username'] ?? 'не установлен') . "\n\n";

// Функция для получения диалогов
function getDialogs($MadelineProto, $limit = 10) {
    echo "💬 Получаю список диалогов...\n\n";
    
    $dialogs = $MadelineProto->messages->getDialogs([
        'offset_date' => 0,
        'offset_id' => 0,
        'offset_peer' => ['_' => 'inputPeerEmpty'],
        'limit' => $limit,
        'hash' => 0,
    ]);
    
    $count = 1;
    foreach ($dialogs['dialogs'] as $dialog) {
        $peer = $dialog['peer'];
        
        // Обработка каналов/групп
        if (isset($peer['channel_id'])) {
            foreach ($dialogs['chats'] as $chat) {
                if ($chat['id'] === $peer['channel_id']) {
                    echo "$count. 👥 " . $chat['title'] . "\n";
                    $count++;
                    break;
                }
            }
        }
        
        // Обработка личных диалогов
        if (isset($peer['user_id'])) {
            foreach ($dialogs['users'] as $user) {
                if ($user['id'] === $peer['user_id']) {
                    $name = $user['first_name'] . " " . ($user['last_name'] ?? '');
                    echo "$count. 👤 $name";
                    if (isset($user['username'])) {
                        echo " (@" . $user['username'] . ")";
                    }
                    echo "\n";
                    $count++;
                    break;
                }
            }
        }
    }
    echo "\n";
}

// Функция для отправки сообщения
function sendMessage($MadelineProto, $peer, $text) {
    try {
        $MadelineProto->messages->sendMessage([
            'peer' => $peer,
            'message' => $text,
        ]);
        echo "✅ Сообщение отправлено!\n";
        return true;
    } catch (Exception $e) {
        echo "❌ Ошибка: " . $e->getMessage() . "\n";
        return false;
    }
}

// Функция для получения последних сообщений
function getMessages($MadelineProto, $peer, $limit = 10) {
    try {
        $messages = $MadelineProto->messages->getHistory([
            'peer' => $peer,
            'limit' => $limit,
        ]);
        
        echo "\n📬 Последние сообщения:\n\n";
        
        foreach (array_reverse($messages['messages']) as $msg) {
            if ($msg['_'] === 'message') {
                $text = $msg['message'] ?? '';
                $out = $msg['out'] ?? false;
                $prefix = $out ? "➡️ Вы:" : "⬅️ Собеседник:";
                echo "$prefix $text\n";
            }
        }
        echo "\n";
    } catch (Exception $e) {
        echo "❌ Ошибка: " . $e->getMessage() . "\n";
    }
}

// Примеры использования:

echo "=== ПРИМЕРЫ ИСПОЛЬЗОВАНИЯ ===\n\n";

// 1. Получить список диалогов
getDialogs($MadelineProto, 5);

// 2. Отправить сообщение (раскомментируйте и замените username)
sendMessage($MadelineProto, '@ruant02', 'Привет из MadelineProto!');

// 3. Получить последние сообщения с пользователем
// getMessages($MadelineProto, '@username', 10);

// 4. Получить информацию о пользователе
 $info = $MadelineProto->getInfo('@ruant02');
 print_r($info);

echo "✅ Готово! Скрипт завершён.\n";
echo "💡 Раскомментируйте нужные функции для тестирования.\n";