<?

/**
 * Copyright 2013 SendReach.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */


// set your API details here
// You can find your userid under SendReach.com > Account Settings > Developers
$api_vars['key'] = '';
$api_vars['secret'] = '';
$api_vars['userid'] = ''; // this is the userid that created the API application.

// include the api classes file
require_once('classes.php');

//////////////////////////////////////////////////
// create a new api instance /////////////////////
//////////////////////////////////////////////////
$sendreach = new api();

////////////////////////////////////////////////////////////////////////////////////////////////////
// get details for a list
////////////////////////////////////////////////////////////////////////////////////////////////////

// set vars
// $list_id = ''; // required

// $list_details = $sendreach->list_details($list_id); // the data is returned in json format
// $list_details = json_decode($list_details); // here we convert the json data into a PHP array

// display the results
// echo '<pre>';
// print_r($list_details);
// echo '</pre><hr>';

////////////////////////////////////////////////////////////////////////////////////////////////////
// get size of a list
////////////////////////////////////////////////////////////////////////////////////////////////////

// set vars
// $list_id = ''; // required

// $list_size = $sendreach->list_size($list_id); // the data is returned in json format
// $list_size = json_decode($list_size); // here we convert the json data into a PHP array

// display the results
// echo '<pre>';
// print_r($list_size);
// echo '</pre><hr>';

////////////////////////////////////////////////////////////////////////////////////////////////////
// get subscribers in a list
////////////////////////////////////////////////////////////////////////////////////////////////////

// set vars
// $list_id = ''; // required

// $list_subscribers = $sendreach->list_subscribers($list_id); // the data is returned in json format
// $list_subscribers = json_decode($list_subscribers); // here we convert the json data into a PHP array

// display the results
// echo '<pre>';
// print_r($list_subscribers);
// echo '</pre><hr>';

////////////////////////////////////////////////////////////////////////////////////////////////////
// create a new list
////////////////////////////////////////////////////////////////////////////////////////////////////

// set new list vars
// $list_name = ''; // letters and numbers only
// $list_redirect = ''; // option, if blank then default llistanimal place holder used. FQDN example: http://domain.com
// $list_from_name = ''; // optional, if blank then default account name used
// $list_from_email = ''; // optional, if blank the default account email used
// $list_optin = 'double'; // optional, if blank then double is selected or use 'single' or 'double'

// $list_create = $sendreach->list_create($list_name,$list_redirect,$list_from_name,$list_from_email,$list_optin); // the data is returned in json format
// $list_create = json_decode($list_create); // here we convert the json data into a PHP array

// display the results
// echo '<pre>';
// print_r($list_create);
// echo '</pre><hr>';

////////////////////////////////////////////////////////////////////////////////////////////////////
// add subscriber to list
////////////////////////////////////////////////////////////////////////////////////////////////////

// set new subscriber vars

// $list_id = ''; // list to subscriber new user too
// $first_name = ''; // optional but highly suggested
// $last_name = ''; // option, but highly suggested
// $email = ''; // required
// $client_ip = ''; // required

// $subscriber_add = $sendreach->subscriber_add($list_id,$first_name,$last_name,$email,$client_ip); // the data is returned in json format
// $subscriber_add = json_decode($subscriber_add); // here we convert the json data into a PHP array


////////////////////////////////////////////////////////////////////////////////////////////////////
// get your lists
////////////////////////////////////////////////////////////////////////////////////////////////////

// set vars
// $lists_view = $sendreach->lists_view(); // the data is returned in json format
// $lists_view = json_decode($lists_view); // here we convert the json data into a PHP array

// display the results
// echo '<pre>';
// print_r($lists_view);
// echo '</pre><hr>';

////////////////////////////////////////////////////////////////////////////////////////////////////
// get details for a subscriber
////////////////////////////////////////////////////////////////////////////////////////////////////

// set vars
// $subscriber_hash = ''; // required, this is the unqiue subscriber hash

// $subscriber_view = $sendreach->subscriber_view($subscriber_hash); // the data is returned in json format
// $subscriber_view = json_decode($subscriber_view); // here we convert the json data into a PHP array

// display the results
// echo '<pre>';
// print_r($subscriber_view);
// echo '</pre>';

////////////////////////////////////////////////////////////////////////////////////////////////////
// unsubscribe a subscriber
////////////////////////////////////////////////////////////////////////////////////////////////////

// set vars
// $subscriber_hash = ''; // required, this is the unqiue subscriber hash

// $subscriber_unsubscribe = $sendreach->subscriber_unsubscribe($subscriber_hash); // the data is returned in json format
// $subscriber_unsubscribe = json_decode($subscriber_unsubscribe); // here we convert the json data into a PHP array

// display the results
// echo '<pre>';
// print_r($subscriber_unsubscribe);
// echo '</pre>';

////////////////////////////////////////////////////////////////////////////////////////////////////
// add new broadcast
////////////////////////////////////////////////////////////////////////////////////////////////////

// set new broadcast vars

// $name = ''; // optional but highly suggested
// $subject = ''; // optional but highly suggested
// $message = ''; // required
// $sms_message = ''; // option

// $broadcast_add = $sendreach->broadcast_add($name,$subject,$message,$sms_message); // the data is returned in json format
// $broadcast_add = json_decode($broadcast_add); // here we convert the json data into a PHP array

////////////////////////////////////////////////////////////////////////////////////////////////////
// view a broadcast
////////////////////////////////////////////////////////////////////////////////////////////////////

// set broadcast vars

// $broadcast_id = ''; // required

// $broadcast_view = $sendreach->broadcast_view($broadcast_id); // the data is returned in json format
// $broadcast_view = json_decode($broadcast_view); // here we convert the json data into a PHP array

////////////////////////////////////////////////////////////////////////////////////////////////////
// send a broadcast
////////////////////////////////////////////////////////////////////////////////////////////////////

// set broadcast vars

// $broadcast_id = ''; // required
// $broadcast_type = ''; // required - 'mail' or 'sms'
// $list_id = ''; // required

// $broadcast_send = $sendreach->broadcast_send($broadcast_id,$broadcast_type,$list_id); // the data is returned in json format
// $broadcast_send = json_decode($broadcast_send); // here we convert the json data into a PHP array