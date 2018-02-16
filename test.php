<?php

declare(strict_types = 1);

require_once __DIR__ . '/vendor/autoload.php';

use WarriorXK\PHPProtoGen;

$generator = new PHPProtoGen\Generator();
$generator->addFile($file1 = new PHPProtoGen\File('root'));

$message1 = new PHPProtoGen\Message('TestMessage');
$message1->addField(new PHPProtoGen\Field(
    'oldField',
    PHPProtoGen\FieldType::Any(),
    PHPProtoGen\Field::OPTION_DEPRECATED
));
$message1->addField(new PHPProtoGen\Field(
   'newField',
   PHPProtoGen\FieldType::Int()
));

$file1->addMessage($message1);

$message2 = new PHPProtoGen\Message('TestMessage2');
$message2->addField(new PHPProtoGen\Field(
    'Whaat',
    PHPProtoGen\FieldType::Bool()
));
$message2->addField(new PHPProtoGen\Field(
    'Nooooo',
    PHPProtoGen\FieldType::String()
));

$file1->addMessage($message2);

var_dump($file1->exportToString());
