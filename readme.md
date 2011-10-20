Tropo + Pusher Mashup
====================

A speech recognition / multi-lingual text-to-speech app that runs on the Tropo platform.  Uses [Pusher](http://pusher.com/) to send realtime data to a web page instance based on a caller's spoken choices.

Requirements
===========

* A Tropo account - signup is [free](https://www.tropo.com/account/register.jsp).
* A Pusher account - signup and pricing [here](http://pusher.com/pricing#showsignup).

Add your Pusher credentials to the file 'tropo-pusher.php', and add your Pusher App ID in the file 'pusher.html'.

Create a new Tropo WebAPI application and point the start URL for your application to the URL of the 'tropo-pusher.php' app on your web server.

Currently supports US, UK and Spanish ASR & TTS. 

Credits
======

* [Tropo WebAPI library](https://github.com/tropo/tropo-webapi-php).
* [Limonade PHP microframework](https://github.com/sofadesign/limonade/).
* [Pusher PHP library](https://github.com/squeeks/Pusher-PHP).

