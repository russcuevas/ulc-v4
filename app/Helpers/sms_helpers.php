<?php

if (!defined('USE_SPECIFIED')) {
    define('USE_SPECIFIED', 0);
}
if (!defined('USE_ALL_DEVICES')) {
    define('USE_ALL_DEVICES', 1);
}
if (!defined('USE_ALL_SIMS')) {
    define('USE_ALL_SIMS', 2);
}

if (!defined('SERVER')) {
    define('SERVER', env('SMS_GATEWAY_SERVER_URL', 'https://app.sms8.io'));
}
if (!defined('API_KEY')) {
    define('API_KEY', env('SMS_GATEWAY_API_KEY', '41b3df67ff4055a740f99aa730ff171f349c5e2b'));
}

if (!function_exists('formatPhoneNumber')) {
    function formatPhoneNumber($number)
    {
        // Remove all non-numeric characters except +
        $number = preg_replace('/[^0-9+]/', '', $number);

        // If it starts with 0 (e.g. 09495748302), replace the leading 0 with +63
        if (strpos($number, '0') === 0) {
            $number = '+63' . substr($number, 1);
        }
        // If it starts with 9 and is 10 digits long (e.g. 9495748302), prepend +63
        elseif (preg_match('/^9[0-9]{9}$/', $number)) {
            $number = '+63' . $number;
        }
        // If it starts with 639 and is 12 digits long (e.g. 639495748302), prepend +
        elseif (preg_match('/^639[0-9]{9}$/', $number)) {
            $number = '+' . $number;
        }

        return $number;
    }
}

if (!function_exists('_executeSmsCurl')) {
    function _executeSmsCurl(string $url, array $postData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("HTTP request failed with code " . $httpCode . ". Response: " . $response);
        }

        $res = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON response from SMS Gateway: " . $response);
        }

        if (isset($res['success']) && !$res['success']) {
            throw new \Exception($res['error']['message'] ?? 'Unknown gateway error');
        }

        return $res;
    }
}

if (!function_exists('sendMessages')) {
    function sendMessages(array $messages, int $option = USE_SPECIFIED, $devices = [], ?int $schedule = null, bool $useRandomDevice = false)
    {
        $apiKey = API_KEY;
        $serverUrl = SERVER;

        // Automatically format each phone number in the batch to +63...
        foreach ($messages as &$msg) {
            if (isset($msg['number'])) {
                $msg['number'] = formatPhoneNumber($msg['number']);
            }
        }
        unset($msg);

        // Kung iisa lang ang mensahe o may manual schedule nang kasama, gamitin ang dating bulk method
        if (count($messages) <= 1) {
            $postData = [
                'key' => $apiKey,
                'messages' => json_encode($messages),
                'option' => $option,
                'devices' => json_encode(is_array($devices) ? $devices : [$devices]),
                'useRandomDevice' => $useRandomDevice ? 1 : 0
            ];

            if ($schedule !== null) {
                $postData['schedule'] = $schedule;
            }

            return _executeSmsCurl(rtrim($serverUrl, '/') . '/services/send.php', $postData);
        }

        // Kung marami ang mensahe, i-schedule natin ang bawat isa ng may delay
        // para hindi sila magsabay-sabay at hindi ma-block ng network operator.
        $delayIncrement = 0; // Segundo na delay sa pagitan ng bawat SMS (maaari mong palitan ito kung nais mo)
        $baseSchedule = $schedule ?? time();
        $lastResult = null;

        foreach ($messages as $index => $msg) {
            $postData = [
                'key' => $apiKey,
                'messages' => json_encode([$msg]),
                'option' => $option,
                'devices' => json_encode(is_array($devices) ? $devices : [$devices]),
                'useRandomDevice' => $useRandomDevice ? 1 : 0
            ];

            // Ang unang mensahe ay ipapadala agad, ang susunod ay may karagdagang delay kung may delayIncrement
            if ($schedule !== null || ($index > 0 && $delayIncrement > 0)) {
                $postData['schedule'] = $baseSchedule + ($index * $delayIncrement);
            }

            try {
                $lastResult = _executeSmsCurl(rtrim($serverUrl, '/') . '/services/send.php', $postData);
            } catch (\Exception $e) {
                // Fail silently / ipagpatuloy ang pag-send sa iba kahit may sumablay na isa
            }
        }

        return $lastResult;
    }
}

if (!function_exists('sendMessageToContactsList')) {
    function sendMessageToContactsList(int $listID, string $message, int $option = USE_SPECIFIED, $devices = [], ?int $schedule = null, bool $isMMS = false, ?string $attachments = null)
    {
        $apiKey = API_KEY;
        $serverUrl = SERVER;

        $postData = [
            'key' => $apiKey,
            'listID' => $listID,
            'message' => $message,
            'option' => $option,
            'devices' => json_encode(is_array($devices) ? $devices : [$devices]),
            'type' => $isMMS ? 'mms' : 'sms',
            'attachments' => $attachments
        ];

        if ($schedule !== null) {
            $postData['schedule'] = $schedule;
        }

        return _executeSmsCurl(rtrim($serverUrl, '/') . '/services/send.php', $postData);
    }
}
