<?php

$file = __DIR__ . '/records.config.default';

if (false === file_exists($file)) {
    die('records.config.default not found');
}

$ruleRegex = '#CONFIG ([\w\._]+) (INT|FLOAT|STRING) (.*)#';

$matches = [];
$content = file_get_contents($file);
preg_match_all($ruleRegex, $content, $matches, PREG_SET_ORDER);

$varDicts = [];
foreach ($matches as $match) {
    list ($rule, $varName, $type, $default) = $match;
    
    $varParts = explode('.', $varName);
    $baseVar = '';
    for ($i=0; $i<count($varParts)-1; $i++) {
        if ($i > 0) {
            $baseVar .= '.';
        }
        $baseVar .= $varParts[$i];
        
        $varDicts[$baseVar] = true;
    }
}

foreach ($matches as $match) {
    list ($rule, $varName, $type, $default) = $match;
    
    $varParts = explode('.', $varName);
    $jinjaVar = '{{ (ats_records.' . $varName;
    if (true === isset($varDicts[$varName])) {
        $jinjaVar .= '.value';
        $varParts[] = 'value';
    }
    
    $baseVar = 'ats_records';
    for ($i=0; $i<count($varParts)-1; $i++) {
        $part = $varParts[$i];
        if ($i === 0) {
            $jinjaVar .= ' if ';
        } else {
            $jinjaVar .= ' and ';
        }
        $baseVar .= '.' . $part;
        $jinjaVar.= $baseVar . ' is defined';
    }
    
    $jinjaVar .= ') | default("' . $default . '") }}';
    
    $content = str_replace(
        $rule,
        'CONFIG ' . $varName . ' ' . $type . ' ' . $jinjaVar,
        $content
    );
}

echo $content;