<?php
/**
 * Middleware/lib/Auth_msoauth.php
 *
 * Class to handle authentication of an application user against a centralized
 * authentication server.
 *
 * @package MiddleWare
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare;

define('APP_OAUTH_SESSION_KEY', 'oauth');

define('MICROSOFT_LOGIN_URL', 'https://login.microsoftonline.com/');

class Auth 
{
    protected $sessionKey;
    protected $oauthLoaded;
    protected $oauthUUID;
    protected $oauthCodeVerifier;

    //----------------------------------------------------------------------
    //-- Constructor

        public function __construct()
        {
            $this->sessionKey = 'appauth';

            if (defined('APP_AUTH_SESSION_KEY')) {
                $this->sessionKey = APP_AUTH_SESSION_KEY;
            }

            $this->resetOAuth();
        }


    //----------------------------------------------------------------------
    //-- Reset the oauth values
    
        protected function resetOAuth()
        {
            $this->oauthLoaded       = false;
            $this->oauthUUID         = '';
            $this->oauthCodeVerifier = '';
        }


    //----------------------------------------------------------------------
    //-- Clear the auth session variable

        public function clear()
        {
            unset($_SESSION[$this->sessionKey]);
        }


    //----------------------------------------------------------------------
    //-- Attempt to load the OAuth values from the session

        public function loadSessionOAuth()
        {
            $this->resetOAuth();

            if (! array_key_exists(APP_OAUTH_SESSION_KEY, $_SESSION)) {
                return false;
            }

            $oauth = $_SESSION[APP_OAUTH_SESSION_KEY];

            if (! is_array($oauth)) {
                return false;
            }

            if (! array_key_exists('uuid', $oauth)) {
                return false;
            }

            if (! array_key_exists('codeVerifier', $oauth)) {
                return false;
            }

            $this->oauthLoaded       = true;
            $this->oauthUUID         = trim($oauth['uuid']);
            $this->oauthCodeVerifier = trim($oauth['codeVerifier']);

            return true;
        }


    //----------------------------------------------------------------------
    //-- Save the OAuth values into the session

        protected function saveSessionOAuth()
        {
            $oauth = array(
                'uuid'         => trim($this->oauthUUID),
                'codeVerifier' => trim($this->oauthCodeVerifier)
            );

            $_SESSION[APP_OAUTH_SESSION_KEY] = $oauth;
        }


    //----------------------------------------------------------------------
    //-- Destroy the OAuth session

        public function destroySessionOAuth()
        {
            unset($_SESSION[APP_OAUTH_SESSION_KEY]);
            $this->resetOAuth();
        }


    //----------------------------------------------------------------------
    //-- Attempt to Process an OAuth Token Response

        protected function processTokenResponse($inResponse = false)
        {
            if ($inResponse === false) { 
                return false;
            }

            $reply = json_decode($inResponse);

            if ($reply == null) {
                return false;
            }

            if (! property_exists($reply, 'token_type')) {
                return false;
            }

            if ($reply->token_type != 'Bearer') {
                return false;
            }

            if (! property_exists($reply, 'expires_in')) {
                return false;
            }

            if (! property_exists($reply, 'access_token')) {
                return false;
            }

            if (! property_exists($reply, 'refresh_token')) {
                return false;
            }

            if (! property_exists($reply, 'id_token')) {
                return false;
            }

            $idToken = base64_decode(explode('.', $reply->id_token)[1]);

            $idInfo = json_decode($idToken);

            if ($idInfo === null) {
                return false;
            }

            if (! property_exists($idInfo, 'name')) {
                return false;
            }
        
            if (! property_exists($idInfo, 'preferred_username')) {
                return false;
            }

            $query = 'SELECT * FROM ' . APP_MYSQL_IDENTITY_DB . '.msoauth WHERE uuid = :uuid';

            $params = array(':uuid' => $this->oauthUUID);

            $record = \Framework\LibPDO::fetchRecord($query, $params);

            if ($record === false) {
                return false;
            }

            $query = 'UPDATE ' . APP_MYSQL_IDENTITY_DB . '.msoauth SET email = :email, name = :name WHERE uuid = :uuid';

            $params = array(
                ':email' => $idInfo['preferred_username'],
                ':name'  => $idInfo['name'],
                ':uuid'  => $this->oauthUUID
            );

            if (! \Framework\LibPDO::execute($query, $params)) {
                return false;
            }

            $this->oauthUUID         = '';
            $this->oauthCodeVerifier = '';
            $this->saveSessionOAuth();

            return true;
        }


    //----------------------------------------------------------------------
    //-- Generate a challenge value

        protected function generateChallenge()
        {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-._~';
            $charLen = strlen($chars) - 1;

            $verifier = '';
            for ($i = 0; $i < 128; $i++) {
                $verifier .= $chars[mt_rand(0, $charLen)];
            }

            $this->oauthCodeVerifier = $verifier;

            // Challenge = Base64 Url Encode ( SHA256 ( Verifier ) )
            // Pack (H) to convert 64 char hash into 32 byte hex
            // As there is no B64UrlEncode we use strtr to swap +/ for -_ and then strip off the =

            return str_replace('=', '', strtr(base64_encode(pack('H*', hash('sha256', $verifier))), '+/', '-_'));
        }


    //----------------------------------------------------------------------
    //-- Generate an OAuth request

        private function generateRequest($data) {
            if (APP_OAUTH_METHOD == 'certificate') {
                // https://docs.microsoft.com/en-us/azure/active-directory/develop/active-directory-certificate-credentials
                $cert = file_get_contents(APP_OAUTH_AUTH_CERTFILE);
                $certKey = openssl_pkey_get_private(file_get_contents(APP_OAUTH_AUTH_KEYFILE));
                $certHash = openssl_x509_fingerprint($cert);
                $certHash = base64_encode(hex2bin($certHash));
                $caHeader = json_encode(array('alg' => 'RS256', 'typ' => 'JWT', 'x5t' => $certHash));
                $caPayload = json_encode(array('aud' => MICROSOFT_LOGIN_URL . APP_OAUTH_TENANTID . '/v2.0',
                                        'exp' => date('U', strtotime('+10 minute')),
                                        'iss' => APP_OAUTH_CLIENTID,
                                        'jti' => $this->uuid(),
                                        'nbf' => date('U'),
                                        'sub' => APP_OAUTH_CLIENTID));
                $caSignature = '';

                $caData = $this->base64UrlEncode($caHeader) . '.' . $this->base64UrlEncode($caPayload);
                openssl_sign($caData, $caSignature, $certKey, OPENSSL_ALGO_SHA256);
                $caSignature = $this->base64UrlEncode($caSignature);
                $clientAssertion = $caData . '.' . $caSignature;
                return $data . '&client_assertion=' . $clientAssertion . '&client_assertion_type=urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
            } else {
                // Use the client secret instead
                return $data . '&client_secret=' . urlencode(APP_OAUTH_SECRET);
            }
        }


    //----------------------------------------------------------------------
    //-- POST the request and await the response

        private function postRequest($endpoint, $data) {
            $ch = \curl_init(MICROSOFT_LOGIN_URL . APP_OAUTH_TENANTID . '/oauth2/v2.0/' . $endpoint);
            \curl_setopt($ch, CURLOPT_POST, 1);
            \curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = \curl_exec($ch);
            if ($cError = \curl_error($ch)) {
                return false;
            }
            \curl_close($ch);

            return $response;
        }


    //----------------------------------------------------------------------
    //-- base64 encode a value

        function base64UrlEncode($toEncode) {
            return str_replace('=', '', strtr(base64_encode($toEncode), '+/', '-_'));
        }


    //----------------------------------------------------------------------
    //-- Generate a uuid value

        function uuid() {
            // uuid function is not my code, but unsure who the original author is. KN
            // uuid version 4
            return strtoupper(sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                // 16 bits for "time_mid"
                mt_rand( 0, 0xffff ),
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand( 0, 0x0fff ) | 0x4000,
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand( 0, 0x3fff ) | 0x8000,
                // 48 bits for "node"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
            ));
        }


    //----------------------------------------------------------------------
    //-- Determine if the OAuth time has expired

        public function isActiveSession()
        {
            if ($this->oauthLoaded !== true) {
                return false;
            }

            if ($this->_oauth_accessToken == '') {
                return false;
            }

            if ($this->_oauth_expires < strtotime('+10 minutes')) {
                if (! $this->refreshToken()) {
                    $this->resetOAuth();
                    $this->saveSessionOAuth();
                    return false;
                }
            }

            return true;
        } // function: isActiveSession


    //----------------------------------------------------------------------
    //--Initiate an OAuth authentication

        public function initiate(string $inUUID = '')
        {

            if ($inUUID == '') {
                $inUUID = $this->uuid();
            }

            $this->resetOAuth();

            $oauthChallenge = $this->generateChallenge();

            $this->oauthUUID = $inUUID;

            $this->saveSessionOAuth();

            $oAuthURL = MICROSOFT_LOGIN_URL . APP_OAUTH_TENANTID . '/oauth2/v2.0/authorize?' .
                            'response_type=code&client_id=' . APP_OAUTH_CLIENTID . 
                            '&redirect_uri=' . urlencode(\Framework\siteURL(APP_OAUTH_REDIRECT_PATH)) .
                            '&scope=' . APP_OAUTH_SCOPE .
                            '&code_challenge=' . $oauthChallenge .
                            '&code_challenge_method=S256';

            header('Location: ' . $oAuthURL);
        }


    //----------------------------------------------------------------------
    //-- Request the Access Token from Microsoft

        public function getAccessToken()
        {
            if ($this->oauthLoaded !== true) {
                return false;
            }

            //-- make request to Microsoft to get token

                $oauthRequest = $this->generateRequest('grant_type=authorization_code&' .
                                    'client_id=' . APP_OAUTH_CLIENTID .
                                    '&redirect_uri=' . urlencode(\Framework\siteURL('msoauth')) .
                                    '&code=' . $_GET['code'] .
                                    '&code_verifier=' . $_SESSION[APP_OAUTH_SESSION_KEY]['codeVerifier']);

                $response = $this->postRequest('token', $oauthRequest);

            //-- process the token to make sure everything is OK

                if ($response === false) { 
                    return false;
                }

                $reply = json_decode($response);

                if ($reply == null) {
                    return false;
                }

                if (! property_exists($reply, 'token_type')) {
                    return false;
                }

                if ($reply->token_type != 'Bearer') {
                    return false;
                }

                if (! property_exists($reply, 'expires_in')) {
                    return false;
                }

                if (! property_exists($reply, 'access_token')) {
                    return false;
                }

                if (! property_exists($reply, 'refresh_token')) {
                    return false;
                }

                if (! property_exists($reply, 'id_token')) {
                    return false;
                }

                $idToken = base64_decode(explode('.', $reply->id_token)[1]);

                $idInfo = json_decode($idToken);

                if ($idInfo === null) {
                    return false;
                }

                if (! property_exists($idInfo, 'name')) {
                    return false;
                }
        
                if (! property_exists($idInfo, 'preferred_username')) {
                    return false;
                }

            //-- clean up and return idInfo

                $this->oauthUUID         = '';
                $this->oauthCodeVerifier = '';
                $this->saveSessionOAuth();

                return $idInfo;
        }


    //----------------------------------------------------------------------
    //-- Destroy the OAuth session and redirect to Microsoft Logout

        public function logout()
        {
            $this->destroySessionOAuth();
            header('Location: ' . MICROSOFT_LOGIN_URL . 'common/wsfederation?wa=wsignout1.0');
        }


    //----------------------------------------------------------------------
    //-- Dump OAuth information

        public function dumpOAuth()
        {
            print '<pre>';
            print 'loaded:              ' . (($this->oauthLoaded) ? 'true' : 'false') . "\n";
            print 'expires:             ' . $this->_oauth_expires . "\n";
            print 'redirect:            ' . $this->_oauth_redirect . "\n";
            print 'accessToken:         ' . $this->_oauth_accessToken . "\n";
            print 'idToken:             ' . $this->_oauth_idToken . "\n";
            print 'refreshToken:        ' . $this->_oauth_refreshToken . "\n";
            print 'codeVerifier:        ' . $this->oauthCodeVerifier . "\n";
            print 'user name:           ' . $this->_oauth_userName . "\n";
            print 'user preferred name: ' . $this->_oauth_userPreferredName . "\n";
            print '</pre>';
        }

}


