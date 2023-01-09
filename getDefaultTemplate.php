<?php
header('Access-Control-Allow-Origin: *');

$default_template_xml_path = "../data/defaultTemplate.xml";

function getDefaultTemplate($default_template_xml_path)
{
    $dom = new domdocument();
    $dom->load($default_template_xml_path);
    $template = $dom->getElementsByTagName('template');

    $name = $template[0]->getAttribute('name');

    return $name;
}

$template = getDefaultTemplate($default_template_xml_path);

echo $template;
