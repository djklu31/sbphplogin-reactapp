<?php
header('Access-Control-Allow-Origin: *');

$default_template_xml_path = "../data/defaultTemplate.xml";
$template_name = $_POST['templatename'];

function overwriteDefaultTemplate($default_template_xml_path, $template_name)
{
    $dom = new domdocument();
    $dom->load($default_template_xml_path);
    $template = $dom->getElementsByTagName('template');
    $template[0]->setAttribute('name', $template_name);

    echo 'Wrote: ' . $dom->save($default_template_xml_path) . ' bytes';
    return $dom;
}

overwriteDefaultTemplate($default_template_xml_path, $template_name);

echo "Default Template Changed";
