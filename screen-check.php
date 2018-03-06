<?php

class QuickConnectScraper {

    // Class constructor method
    function __construct() {

        $this->useragent = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3';    // Setting useragent of a popular browser

        $handle = fopen('cookie.txt', 'w') or exit('Unable to create or open cookie.txt file.'."\n");   // Opening or creating cookie file
        fclose($handle);               // Closing cookie file
        $this->cookie = 'cookie.txt';  // Setting a cookie file to store cookie
        $this->timeout = 30;           // Setting connection timeout in seconds

        $this->loginUrl = 'http://ianhuet.quickconnect.to';
    }


    // User login method
    public function login() {

        // Login values to POST as array
        $postValues = http_build_query(
            array(
                'username' => $emailAddress,
                'password' => $password,
                'RememberMe' => 'true',
                'IsAjaxRequest' => 'false'
            )
        );

        $request = $this->curlPostFields($this->loginUrl, $postValues);   // Making cURL POST request

        $login = json_decode($request); // Decoding the JSON response

        if ($login->success == 1) {
            // Successful login
            $message = 'Successful login.'; // Assigning successful message
            echo $message;
        } elseif ($login->success == 0) {
            $message = $login->error;    // Assigning login error message returned by server
            echo $message;
            exit(); // Ending program
        } else {
            $message = 'Unknown login error.';  // Assigning unknown login error message
            echo $message;
            exit(); // Ending program
        }
    }

    // User logout method
    public function logout() {
        $request = $this->curlPostFields('https://mystore.ncrsilver.com/app/Account/LogOff?CancelLogin=true&isAjaxRequest=true', null);  // Logging out
    }

    // Method to search and scrape existing members details
    public function scrapePersons($searchString = '') {

        $searchUrl = 'https://mystore.ncrsilver.com/app/Customer/GetCustomers';

        $postValues = array(
            'PageRowCount' => 1000,
            'RequestedPageNum' => 1,
            'TotalRowCount' => -1,
            'SearchArg' => $searchString,
            'SortDirection' => 'ASC',
            'SortColumn' => 'Name',
            'page' => 1,
            'start' => 0,
            'limit' => 1000,
            'sort' => '[{"property":"Name","direction":"ASC"}]',
            'isAjaxRequest' => true,
        );

        $search = $this->curlPostFields($searchUrl, $postValues);

        return $search;
    }

    // Method to make a POST request using form fields
    public function curlPostFields($postUrl, $postValues) {
        $_ch = curl_init(); // Initialising cURL session

        // Setting cURL options
        curl_setopt($_ch, CURLOPT_SSL_VERIFYPEER, FALSE);   // Prevent cURL from verifying SSL certificate
        curl_setopt($_ch, CURLOPT_FAILONERROR, TRUE);   // Script should fail silently on error
        curl_setopt($_ch, CURLOPT_COOKIESESSION, TRUE); // Use cookies
        curl_setopt($_ch, CURLOPT_FOLLOWLOCATION, TRUE);    // Follow Location: headers
        curl_setopt($_ch, CURLOPT_RETURNTRANSFER, TRUE);    // Returning transfer as a string
        curl_setopt($_ch, CURLOPT_COOKIEFILE, $this->cookie);    // Setting cookiefile
        curl_setopt($_ch, CURLOPT_COOKIEJAR, $this->cookie); // Setting cookiejar
        curl_setopt($_ch, CURLOPT_USERAGENT, $this->useragent);  // Setting useragent
        curl_setopt($_ch, CURLOPT_URL, $postUrl);   // Setting URL to POST to
        curl_setopt($_ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);   // Connection timeout
        curl_setopt($_ch, CURLOPT_TIMEOUT, $this->timeout); // Request timeout

        curl_setopt($_ch, CURLOPT_POST, TRUE);  // Setting method as POST
        curl_setopt($_ch, CURLOPT_POSTFIELDS, $postValues); // Setting POST fields (array)

        $results = curl_exec($_ch); // Executing cURL session
        curl_close($_ch);   // Closing cURL session

        return $results;
    }


    // Class destructor method
    function __destruct() {
        // Empty
    }
}


// Let's run this baby and scrape us some data!
$testScrape = new QuickConnectScraper();   // Instantiating new object

$testScrape->login();    // Logging into server

$data = json_decode($testScrape->scrapePersons());   // Scraping people records
print_r($data);

$testScrape->logout();   // Logging out
