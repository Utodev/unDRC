<?php

$output = '';

define('MAX_SYSMESS',62);
define('MAX_DIRECTION', 20);
define('MESSAGE_OPCODE',38);
define('SYSMESS_OPCODE',54);
define('MES_OPCODE', 77);
define('DESC_OPCODE', 19);
$messageOpcodes = array(MESSAGE_OPCODE, SYSMESS_OPCODE, MES_OPCODE, DESC_OPCODE);


$specialLocs = array(
    252 => 'NOT_CREATED',
    253 => 'WORN',
    254 => 'CARRIED',
    255 => 'HERE'
    );

$languages = array(
    0 => "English",
    1 => "Spanish"
    );
    

$condactWithVocabularyParameter = array(
//      array(param.no => wordType)
    "36"=>array(0 => 0, 1 => 2), // SYNONYM
    "17"=>array(0 => 1), // ADVERB
    "69"=>array(0 => 2), // NOUN2
    "16"=>array(0 => 3), // ADJECT1
    "70"=>array(0 => 3), // ADJECT2
    "68"=>array(0 => 4)  // PREP
);
    

function prettyHex($num)
{
    return sprintf("%04X", $num) . 'h (' . $num . ')';
}


function replaceExtension($filename, $new_extension) {
    $info = pathinfo($filename);
    return ($info['dirname'] ? $info['dirname'] . DIRECTORY_SEPARATOR : '') 
        . $info['filename'] 
        . '.' 
        . $new_extension;
}


function str2Hex($string)
{
    $result='';
    for ($i=0; $i < strlen($string); $i++)
        $result .=  str_pad(dechex(ord($string[$i])), 2, '0', STR_PAD_LEFT);
    return $result;
}

function printSeparator()
{
    writeText("\n; ---------------------------------------------------------------------------\n\n");
}


function writeText($text)
{
    global $output;
    $output .= $text;
}

function generateSourceCode($data, $noInlineMessages, $noMaluva, $dumpTokens)
{
    global $wordTypes, $specialLocs, $condacts, $languages,$output,$condactWithVocabularyParameter, $messageOpcodes;

    extract($data);
    extract($HEADER);

    // Header data
    writeText("; Source code by unDRC, DAAD v2.0+ Decompiler\n");
    writeText("; Extraction date: " . date('Y-m-d H:i:s') . "\n");
    writeText("; Machine : $machineName\n");
    writeText("; Language: " . $languages[$lang] . "\n");
    
    writeText("\n; Header Info");
    writeText("\n; ===========\n");
    writeText("; Objects: $numObjs\n");
    writeText("; Locations: $numLocs\n");
    writeText("; User messages: $numUserMessages\n");
    writeText("; System messages: $numSystemMessages\n");
    writeText("; Processes: $numProcesses\n");
    writeText("; Base address: " . prettyHex($baseAddress) ."\n");
    writeText('; Endianess: ' . ($isLittleEndian ? 'little' : 'big') . " endian\n");

    writeText("\n; Offsets");
    writeText("\n; =======\n");
    writeText("; Tokens: " . prettyHex($posTokens) ."\n");
    writeText("; Processes: " . prettyHex($posProcesses) ."\n");
    writeText("; Objects: " . prettyHex($posObjects) ."\n");
    writeText("; Locations: " . prettyHex($posLocations) ."\n");
    writeText("; User messages: " . prettyHex($posUserMessages) ."\n");
    writeText("; System messages: " . prettyHex($posSystemMessages) ."\n");
    writeText("; Connections: " . prettyHex($posConnections) ."\n");
    writeText("; Vocabulary: " . prettyHex($posVocabulary) ."\n");
    writeText("; Initialy at: " . prettyHex($posInitialyAt) ."\n");
    writeText("; Objects vocabulary: " . prettyHex($posObjectsVocabulary) ."\n");
    writeText("; Object attributes: " . prettyHex($posObjectAttributes) ."\n");
    writeText("; User object attributes: " . prettyHex($posObjectUserAttributes) ."\n");
    writeText("; File length: " . prettyHex($fileLength) ."\n");


    // CTL
    printSeparator();
    writeText("/CTL\n_\n");

    // TOK - not in DSF format - but export TOK file if requested
    if ($dumpTokens) 
    {
        $DRCtokens = new stdClass();
        $DRCtokens->tokens = array();
        $DRCtokens->compression = 'advanced';
        for($i=0; $i < count($TOK); $i++)
            $DRCtokens->tokens[] = str2hex($tokens[$i]);
        file_put_contents(replaceExtension($filename, 'tok'), json_encode($data['TOK']));
    }

    // VOC
    printSeparator();
    writeText("/VOC  ; -- Vocabulary\n");
    foreach($VOC as $wordType=>$wordTypeArray)
    {
        ksort($wordTypeArray);
        $wordTypeText = $wordTypes[$wordType];
        writeText("\n\n; " .strtoupper($wordTypeText)."S\n");
        foreach($wordTypeArray as $id=>$wordArray)
            foreach($wordArray as $word)
                writeText(str_pad($word,8) . str_pad($wordTypeText,12). "$id\n");
    }

    // If there's going to be inline Messages we need to determine which among all messages, system messages and descriptions
    // are used inline. To do that, we will check which descriptions, messages and system messges are used without indirection
    // in the processes. Those can be used inline. First System messages are an exception.
    if (!$noInlineMessages)
    {
        $usedSYSMES = array();
        $usedUSRMES = array();
        $usedDESC = array();
        foreach($PRO as $process)
            foreach ($process as $entry)
                foreach($entry['condacts'] as $condact)
                {
                    // Please notice  we dont checkj for indirection in the opcode, if there is indirection, the opcode won't macth 
                    // valid opcode and will be ignored, just as we want to be ignored.
                    switch ($condact['opcode']) 
                    {
                        case SYSMESS_OPCODE:
                            $mesno = $condact['params'][0];
                            if ($mesno>MAX_SYSMESS) if (!in_array($mesno, $usedSYSMES)) $usedSYSMES[] = $mesno;
                            break;
                        case MESSAGE_OPCODE:
                        case MES_OPCODE:
                            $mesno = $condact['params'][0];
                            if (!in_array($mesno, $usedUSRMES)) $usedUSRMES[] = $mesno;
                            break;
                        case DESC_OPCODE:
                            $mesno = $condact['params'][0];
                            if (!in_array($mesno, $usedDESC)) $usedDESC[] = $mesno;
                            break;
                    }
                }
    }
   
    // STX
    printSeparator();
    writeText("/STX  ; -- System messages\n");   
    foreach($STX as $mesno=>$message)
    {
        if (!$noInlineMessages && in_array($mesno, $usedSYSMES)) break;
        writeText("/$mesno \"$message\"\n");
    }

    // MTX
    printSeparator();
    writeText("/MTX  ; -- User messages\n");   
    foreach($MTX as $mesno=>$message)
    {
        if (!$noInlineMessages && in_array($mesno, $usedUSRMES)) break;
        writeText("/$mesno \"$message\"\n");
    }


    // OTX
    printSeparator();
    writeText("/OTX  ; -- Object descriptions\n");   
    foreach($OTX as $mesno=>$message)
    {
        writeText("/$mesno \"$message\"\n");
    }

    // LTX
    printSeparator();
    writeText("/LTX  ; -- Locations\n");   
    foreach($LTX as $mesno=>$message)
    {
        if (!$noInlineMessages && in_array($mesno, $usedDESC)) break;
        writeText("/$mesno \"$message\"\n");
    }

    //CON
    printSeparator();
    writeText("/CON  ; -- Connections\n");   
    for ($locno=0;$locno<$numLocs;$locno++)
    {
        $connections = $CON[$locno];
        writeText("/$locno\n");
        foreach($connections as $direction=>$locno2)
        {
            if (isset($VOC[0][$direction])) $word = $VOC[0][$direction][0];
            else if ((isset($VOC[2][$direction])) && ($direction<MAX_DIRECTION)) $word = $VOC[2][$direction][0];
            else $word = "??$direction";
            writeText("$word $locno2\n");
        }
    }

    //OBJ
    printSeparator();
    writeText("/OBJ  ; -- Object data\n\n");   
    writeText(";obj.no  initially@    weight  c w  5 4 3 2 1 0 9 8 7 6 5 4 3 2 1 0     noun       adjective\n");
    foreach($OBJ as $objno=>$objData)
    {
        $objno = str_pad($objno, 4, " ", STR_PAD_RIGHT);
        $initiallyAt = $objData['initiallyAt'];
        if (isset($specialLocs[$initiallyAt])) $initiallyAt = $specialLocs[$initiallyAt];       
        $initiallyAt = str_pad($initiallyAt, 11, " ", STR_PAD_LEFT);
        $weight = str_pad($objData['weight'], 7, " ", STR_PAD_LEFT);
        $container = str_pad($objData['container']?"Y":"_", 2, " ", STR_PAD_RIGHT);
        $wearable = str_pad($objData['wearable']?"Y":"_", 2, " ", STR_PAD_RIGHT);
        
        $noun = $objData['noun'];
        if ($noun == 255) $noun = "_";
        else
        if (isset($VOC[2][$noun])) $noun = $VOC[2][$noun][0]; else $noun = "??$noun";
        $noun = str_pad($noun, 8, " ", STR_PAD_LEFT);

        $adjective = str_pad($objData['adjective'], 8, " ", STR_PAD_LEFT);
        if ($adjective == 255) $adjective = "_";
        else
        if (isset($VOC[3][$adjective])) $adjective = $VOC[2][$adjective][0]; else $adjective = "??$adjective";
        $adjective = str_pad($adjective, 8, " ", STR_PAD_LEFT);
        
       
        $userAttrs = $objData['userAttrs'];
        $userAttrsStr = '';
        for ($j=15;$j>=0;$j--)
           $userAttrsStr .= ($userAttrs& (1<<$j)) ? 'Y ':'_ ';
        

        writeText("$objno     $initiallyAt $weight   $container$wearable $userAttrsStr $noun $adjective\n");
    }

 
    //PRO
    foreach ($PRO as $procno=>$entries)
    {
        printSeparator();
        writeText("/PRO $procno\n");
        foreach ($entries as $entry)
        {
            $verb = $entry['verb'];
            $noun = $entry['noun'];

            if ($verb == 255) $verb = "_";
            else if (isset($VOC[0][$verb])) $verb = $VOC[0][$verb][0]; 
            else if ((isset($VOC[2][$verb])) && ($verb<MAX_DIRECTION)) $verb = $VOC[2][$verb][0];
            else $verb = "??$verb";

            if ($noun == 255) $noun = "_";
            else if (isset($VOC[2][$noun])) $noun = $VOC[2][$noun][0]; 
            else $noun = "??$noun";
            writeText("> " . str_pad($verb, 6, " ", STR_PAD_RIGHT) . " " . str_pad($noun, 6, " ", STR_PAD_RIGHT). " ");

            $entryCondacts = $entry['condacts'];
            foreach($entryCondacts as $condactNum=>$condactData)
            {
                if ($condactNum) writeText("                ");
                $opcode = $condactData['opcode'];
                
                $paramString = '';
                for($i=0;$i<sizeof($condactData['params']);$i++)
                {
                    $param = $condactData['params'][$i];
                    $indirection = $condactData['indirection'];

                    // If it's a condact with vocabulary parameter (NOUN2, PREPE etc.) replace number it with the corresponding vocabulary word
                    if (isset($condactWithVocabularyParameter[$opcode][$i]) && isset($VOC[$condactWithVocabularyParameter[$opcode][$i]][$param][0])) 
                    {
                       if ($indirection) error("Indirection found in a condact whose parameter is a vocabulary word: [$mnemonic] at process $procno\n");
                       $param = $VOC[$condactWithVocabularyParameter[$opcode][$i]][$param][0];
                    }                    

                    // If it's a message condact, and inline messages are allowed, replace message number with the corresponding message
                    if ((!$noInlineMessages) && (in_array($opcode, $messageOpcodes) && (!$indirection))) // Don't make inline messages if asked so, or if there's indirection
                    {
                        switch($opcode)
                        {
                            case MES_OPCODE:
                                $param = '"' . $MTX[$param] . '"' . ' ; MES ' . $param;
                                $opcode = MES_OPCODE;
                                break;
                            case MESSAGE_OPCODE:
                                $param = '"' . $MTX[$param] . '"' . ' ;MESSAGE ' . $param;
                                $opcode = MES_OPCODE;
                                break;
                            case DESC_OPCODE:
                                $param = '"' . $LTX[$param] . '"' . ' ; DESC ' . $param;
                                $opcode = MES_OPCODE;
                            case SYSMESS_OPCODE:
                                if ($param > MAX_SYSMESS) 
                                {
                                    $param = '"' . $STX[$param] . '"' . ' ; SYSMESS ' . $param;
                                    $opcode = MES_OPCODE;
                                } else $param= $param . ' ; ' . $STX[$param];
                                break;
                        }
                    }

                    if ($indirection) $param = "@" . $param;
                    $paramString .= "$param ";
                }
                $mnemonic = $condacts[$opcode][1];
                writeText("$mnemonic $paramString\n");
            }
            writeText("\n\n");
            

        }
    }

    //END
    printSeparator();
    writeText("/END\n");
    return $output;

}

/*         if (isset($wordParamTypes[$opcode][$j]) && isset($VOC[$wordParamTypes[$opcode][$j]][$val][0])) 
         {
            if ($indirection) error("Indirection found in a condact whose parameter is a vocabulary word: [$mnemonic] at entry $entry of process $i\n");
            $params[$j] = $VOC[$wordParamTypes[$opcode][$j]][$val][0];
         }
         else  
         {
            if ((!$noInlineMessages) && (in_array($opcode, $messageOpcodes)&&(!$indirection))) // Don't make inline messages if asked so, or if it's not a "message opcode"
            {
                if (($opcode != $SYSMESS_OPCODE) || (($opcode == $SYSMESS_OPCODE) && ($val>MAX_SYSMESS))) // Also, don't replace original system messages
                {
                    $message = getMessage($opcode, $val);
                    if ($message != '')  
                    {
                        $mnemonic = 'MES';
                        if ((strlen($message)>2) && (substr($message,-2)=='#na')) 
                        {
                            $message = substr($message,0,-2);
                            $mnemonic = 'MESSAGE';
                        }
                        $val = "\"$message\"";
                    }   
                }
                else // If it's an original sysmess, we add the code, but as a comment
                {
                    $message = getMessage($opcode, $val);
                    $val = $val . "; $message";
                }
            }
            $params[$j] = ((($indirection) && ($j==0) ? "@$val" : "$val"));
         }
       }
       
       writeText("        $mnemonic ");
       for ($j = 0; $j < $condacts[$opcode][0]; $j++) writeText($params[$j]." ");
       writeText("\n");
*/
       
