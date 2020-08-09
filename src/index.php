<?php
/**
 * Endpoint for Slack requests
 *
 * @author     Daniel Centore
 * @copyright  2020 Daniel Centore (danielcentore.com)
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html  GPLv3.0
 */
use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Exception\SlackErrorResponse;

require_once 'settings.php';

require_once __DIR__.'/vendor/autoload.php';

// $client contains all the methods to interact with the API
$client = JoliCode\Slack\ClientFactory::create($bot_oauth_token);

$message_log_channel = 'anon-message-log';

$channel_id = $_POST['channel_id'];
$command = $_POST['command'];
$text = $_POST['text'];
$username = $_POST['user_name'];
$user_id = $_POST['user_id'];

if (empty($text)) {
    die("Please include a message. For example: `/a Hello everybody!`");
}

// === Send the message anonymously === //

try {
    // Requires "chat:write" and "chat:write.public"
    $result = $client->chatPostMessage([
        'channel' => $channel_id,
        'text' => $text,
    ]);
} catch (SlackErrorResponse $e) {
    die('Failed to send anonymous message. Mention the bot (Type @Anonymous) to invite it to this chat!');
}

// === Create the logging group === //

try {
    $create = $client->conversationsCreate([
       'name' => $message_log_channel,
       'is_private' => true,
    ]);
} catch (SlackErrorResponse $e) {
    // Swallow the error if the group already exists
}

// === Identify the logging group === //
$groups = [];
$cursor = '';

do {
    // Requires "groups:read" and "channels:read"
    $response = $client->conversationsList([
        'limit' => 200,
        'cursor' => $cursor,
        'types' => 'public_channel,private_channel',
    ]);

    $groups = array_merge($groups, $response->getChannels());
    $cursor = $response->getResponseMetadata() ? $response->getResponseMetadata()->getNextCursor() : '';
} while (!empty($cursor));

$log_group_id = null;
foreach ($groups as $group) {
    if ($group->getName() == $message_log_channel) {
        $log_group_id = $group->getId();
        break;
    }
}
if ($log_group_id == null) {
    die ('Failed to identify log group');
}

// === Add admins to the group === //

$users = [];
$cursor = '';
do {
    // Requires "users:read"
    $response = $client->usersList([
        'limit' => 200,
        'cursor' => $cursor,
    ]);

    $users = array_merge($users, $response->getMembers());
    $cursor = $response->getResponseMetadata() ? $response->getResponseMetadata()->getNextCursor() : '';
} while (!empty($cursor));

$admin_user_ids = [];
if (isset($force_admin)) {
    $admin_user_ids[] = $force_admin;
}
foreach ($users as $user) {
    if ($user->getIsAdmin()) {
        $admin_user_ids[] = $user->getId();
    }
}
// Requires "groups:write"
$client->conversationsInvite([
    'channel' => $log_group_id,
    'users' => implode(',', $admin_user_ids),
]);


// === Log to the logging group === //

// Requires "chat:write"
$result = $client->chatPostMessage([
    'channel' => $message_log_channel,
    'text' => '<@' . $user_id . '|' . $username . '>' . ': ' . $text,
]);
