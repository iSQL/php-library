<?php
/*
| -------------------------------------------------------------------
| EMAIL
| -------------------------------------------------------------------
|
| Developing and testing Email class
|
| -------------------------------------------------------------------
*/
use phplibrary\Email as email;
use phplibrary\Format as format;

$email      = 'Contact@ZlatanStajic.com';
$mailto     = email::mailto($email);
$validated  = empty(email::validate($email)) ? 'Invalid' : 'Valid';

format::pre($validated, TRUE);
format::pre(email::show($email), TRUE);
format::pre($mailto, TRUE);
