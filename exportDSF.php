<?php

$output = '';

define ('OFUSCATE_VALUE', 0xFF);

define('MAX_SYSMESS',62);
define('MAX_DIRECTION', 20);
define('MESSAGE_OPCODE',38);
define('SYSMESS_OPCODE',54);
define('MES_OPCODE', 77);
define('DESC_OPCODE', 19);
define('EXTERN_OPCODE', 61);

// Maluva commands
define('XPICTURE',0);
define('XSAVE',1);
define('XLOAD',2);
define('XMES', 3); 
define('XPART', 4);
define('XBEEP', 5);
define('XSPLITSCR', 6);
define('XUNDONE',7);    
define('XNEXTCLS',8);
define('XNEXTRESET',9);
define('XSPEED',10);
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

$systemFlagsNames = array ("fDark"=>0,"fObjectsCarried"=>1,"fGFlags"=>29,"fScore"=>30,"fTurns"=>31,"fTurnsHi"=>32,"fVerb"=>33,"fNoun"=>34,"fAdject1"=>35,"fAdverb"=>36,"fMaxCarr"=>37,"fPlayer"=>38,"fPrep"=>43,"fNoun2"=>44,"fAdject2"=>45,"fCPronounNoun"=>46,"fCPronounAdject"=>47,"fTimeout"=>48,"fTimeoutFlags"=>49,"fDoallObjNo"=>50,"fRefObject"=>51,"fStrength"=>52,"fObjFlags"=>53,"fRefObjLoc"=>54,"fRefObjWeight"=>55,"fRefObjIsContainer"=>56,"fRefObjisWearable"=>57,"fRefObjAttr1"=>58,"fRefObjAttr2"=>59,"fInkeyKey1"=>60,"fInkeyKey2"=>61,"fScreenMode"=>62,"fCurrentWindow"=>63);
$objectNames = array();    

$condactWithVocabularyParameter = array(
//      array(param.no => wordType)
    "36"=>array(0 => 0, 1 => 2), // SYNONYM
    "17"=>array(0 => 1), // ADVERB
    "69"=>array(0 => 2), // NOUN2
    "16"=>array(0 => 3), // ADJECT1
    "70"=>array(0 => 3), // ADJECT2
    "68"=>array(0 => 4)  // PREP
);

$condactsWithFlagnoParameter = array (
    "27"=>array(0), // DPRINT
    "47"=>array(0), // SET
    "48"=>array(0), // CLEAR
    "49"=>array(0), // PLUS
    "50"=>array(0), // MINUS
    "51"=>array(0), // LET
    "53"=>array(0), // PRINT
    "63"=>array(0), // RAMLOAD
    "71"=>array(0,1), // ADD
    "72"=>array(0,1), // SUB
    "76"=>array(0,1), // SAME
    "79"=>array(0), // NOTEQ
    "80"=>array(0,1), // NOTSAME
    "89"=>array(1), // WEIGH
    "94"=>array(1), // WEIGHT
    "95"=>array(1), // RANDOM
    "106"=>array(0), // MOVE
    "112"=>array(0,1), // BIGGER
    "113"=>array(0,1), // SMALLER
    "119"=>array(1), // COPYOF
    "123"=>array(0), // COPYOF
    "125"=>array(0,1), // COPYFF
    "126"=>array(0,1), // COPYBF
);

$condactsWithObjnoParameter = array (
        "4"=>array(0), // PRESENT
        "5"=>array(0), // ABSENT
        "6"=>array(0), // WORN
        "7"=>array(0), // NOTWORN
        "8"=>array(0), // CARRIED
        "9"=>array(0), // NOTCARR
        "39"=>array(0), // REMOVE
        "40"=>array(0), // GET
        "41"=>array(0), // DROP
        "42"=>array(0), // WEAR
        "43"=>array(0), // DESTROY
        "44"=>array(0), // CREATE
        "45"=>array(0,1), // SWAP
        "46"=>array(0), // PLACE
        "55"=>array(0), // ISAT
        "56"=>array(0), // SETCO
        "88"=>array(0), // ISNOTAT
        "89"=>array(0), // WEIGH
        "90"=>array(0), // PUTIN
        "91"=>array(0), // TAKEOUT
        "119"=>array(0), // COPYOF
        "121"=>array(0,1), // COPYOO
        "123"=>array(1), // COPYFO
);

    
function decodeXMessage($xmessage)
{
    
    global $daad_to_iso8859_15, $TOK;
    $message='';
    $i = 0;
    do
    {
        $c = ord($xmessage[$i]);
        $i++;
        if ($c < 128) 
        {
            $token_id = $c ^ OFUSCATE_VALUE - 128;
            $thetoken = $TOK[$token_id];
            $message.=$thetoken;
        } 
        else 
        {
            $d = $daad_to_iso8859_15[$c];
            if ($d==0x0c) $message.= "#k";
            else if ($d==0x0e) $message.= "#g";
            else if ($d==0x0f) $message.= "#t";
            else if ($d==0x0b) $message.= "#b";
            else if ($d==0x7f) $message.= "#f";
            else if ($d==0x0d) $message.= "#n";
            else if ($d==0x0a) $message.= ""; // This is the mark of end of message, we will not be adding it
            else $message.=chr($d);            
        }
    } while ($c != 0xF5);  // 0x0A xor 255
    $message = str_replace(chr(13), '#n', $message);
    $message = str_replace('"', '\"', $message);
    return $message;
}


function getXmessageByOffset($offset, $machineName)
{
    $size = 0;
    switch ($machineName) 
    {
        case 'ZX'  : 
        case 'MSX' : 
        case 'PCW' : 
        case 'PC'  : $size= 64; break; // For PC, it's safe to assume it's PCDAAD or we would have never got here, so it's 64
        case 'MSX2': 
        case 'HTML': $size= 16; break; 
        case 'CPC' : 
        case 'C64' : 
        case 'CP4' : $size= 2; break;      
        
        default: $size = 0;
    }
    $size  = $size * 1024; // Convert to bytes
    $filename = floor($offset / $size);
    $finaloffset = $offset % $size;
    $extension = ($machineName == 'C64' || $machineName == 'CP4') ? '' : 'XMB';
    $filename = $filename . '.' . $extension; 

    if (!file_exists($filename))
        return "<File $filename not found - XMESSAGE extraction could not be performed>";

    $filehandle = fopen($filename, "rb");
    fseek($filehandle, $finaloffset);
    $xmessage = fread($filehandle, 512);
    fclose($filehandle);
    $xmessage = decodeXMessage($xmessage);   
    return $xmessage;   
}

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


function getObjectIdentifier($message)
{

    $message = str_replace(' ', '', ucwords($message));
    $newmessage = '';
    // remove non standar characters
    for ($i=0;$i<strlen($message);$i++)
        if 
        (
             ((($message[$i])>='A') && (($message[$i])<='Z'))
             ||
             ((($message[$i])>='a') && (($message[$i])<='z'))
             ||
             ((($message[$i])>='0') && (($message[$i])<='9'))
        ) $newmessage.= $message[$i];
       
    // CamelCase
    if ($newmessage!='') return "o$newmessage"; else return "";
}

function addObjectIdentifier($mesno, $message)
{
    global $objectNames;
    $objectIdentifier = getObjectIdentifier($message);
    // Avoid conflict if two objects have the same description
    $i = 2;
    $currentIdentifier = $objectIdentifier;
    while (array_key_exists($currentIdentifier, $objectNames))
    {
        $currentIdentifier .= $i;
        $i++;
    }
    if ($currentIdentifier != '') $objectNames[$currentIdentifier] = $mesno;
}

function generateDSF($data, $inlineMessages, $maluva, $dumpTokens, $objectIdenfiers)
{
    global $wordTypes, $specialLocs, $condacts, $languages,$output,$condactWithVocabularyParameter, $condactsWithFlagnoParameter, $condactsWithObjnoParameter, $messageOpcodes, $systemFlagsNames, $objectNames;

    extract($data);
    extract($HEADER);

    // Header data
    writeText("; Source code by unDRC ".VERSION.", DAAD v2.0+ Decompiler\n");
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
    writeText("/CTL\n_\n\n");
    foreach ($systemFlagsNames as $flagName=>$flagNumber)
        writeText("#define $flagName $flagNumber\n");
    
    if ($objectIdenfiers) 
    {
        writeText("\n; Object identifiers\n");
        foreach($OTX as $mesno=>$message)
        {
            addObjectIdentifier($mesno, $message);
        }
        foreach ($objectNames as $objectName=>$objectNumber)
        writeText("#define $objectName $objectNumber\n");
    
    }
    

    

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
                writeText(str_pad($word,8) . str_pad($id,5). "$wordTypeText\n");
    }


    // If there's going to be inline Messages we need to determine which among all messages, system messages and descriptions
    // are used inline. To do that, we will check which descriptions, messages and system messges are used without indirection
    // in the processes. Those can be used inline. First System messages are an exception.
    /*
    if ($inlineMessages)
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
                    $opcode = $condact['opcode'];
                    if ($condact['indirection']) continue; // No reference to message if it's indirection
                    switch ($opcode) 
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
    */
   
    // STX
    printSeparator();
    writeText("/STX  ; -- System messages\n");   
    foreach($STX as $mesno=>$message)
    {
        //if ($inlineMessages && in_array($mesno, $usedSYSMES)) break;
        writeText("/$mesno \"$message\"\n");
    }

    // MTX
    printSeparator();
    writeText("/MTX  ; -- User messages\n");   
    foreach($MTX as $mesno=>$message)
    {
        //if ($inlineMessages && in_array($mesno, $usedUSRMES)) break;
        writeText("/$mesno \"$message\"\n");
    }


    // OTX
    printSeparator();
    writeText("/OTX  ; -- Object descriptions\n");   
    foreach($OTX as $mesno=>$message)
    {
        writeText("/$mesno \"$message\"\n");
        if ($objectIdenfiers) addObjectIdentifier($mesno, $message);
    }

    // LTX
    printSeparator();
    writeText("/LTX  ; -- Locations\n");   
    
    foreach($LTX as $mesno=>$message)
    {
        //if ($inlineMessages && in_array($mesno, $usedDESC)) break;
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
        if (isset($VOC[3][$adjective])) $adjective = $VOC[3][$adjective][0]; else $adjective = "??$adjective";
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
                    if (($inlineMessages) && (in_array($opcode, $messageOpcodes) && (!$indirection))) // Don't make inline messages if asked so, or if there's indirection
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
                                break;
                            case SYSMESS_OPCODE:
                                if ($param > MAX_SYSMESS) 
                                {
                                    $param = '"' . $STX[$param] . '"' . ' ; SYSMESS ' . $param;
                                    $opcode = MES_OPCODE;
                                } else $param= $param . ' ; ' . $STX[$param];
                                break;
                        }
                    }

                    // replace flag number with the corresponding flag name if exists
                    if (array_key_exists($opcode, $condactsWithFlagnoParameter) && (in_array($i, $condactsWithFlagnoParameter[$opcode])))
                    {
                        $res  = array_search($param, $systemFlagsNames);                          
                        if ($res !== false) $param = $res;
                    }
                    // replace flag number with the corresponding flag name if exists, when indirection
                    if (($indirection) && ($i==0))
                    {
                        $res  = array_search($param, $systemFlagsNames);                          
                        if ($res !== false) $param = $res;
                    }
                    // replace object number with corresponding object identifier if exists
                    if (array_key_exists($opcode, $condactsWithObjnoParameter) && (in_array($i, $condactsWithObjnoParameter[$opcode])) &&(($i>0)||((!$indirection))))
                    {
                        $res  = array_search($param, $objectNames);                          
                        if ($res !== false) $param = $res;
                    }
                  


                    if (($indirection) && ($i==0)) $param = "@" . $param;
                    $paramString .= "$param ";
                }
                $mnemonic = $condacts[$opcode][1];

                // If using Maluva, replace EXTERN calls with MALUVA pseudocondacts
                if ($maluva && ($opcode == EXTERN_OPCODE))
                {
                    $param0 = $condactData['params'][0];
                    $param1 = $condactData['params'][1];
                    if (in_array($param1, array(XMES, XBEEP))) $param2 = $condactData['params'][2];

                    switch ($param1)
                    {
                        case XPICTURE:
                            $mnemonic = "XPICTURE";
                            $paramString = ($indirection?'@':'') . "$param0 ; EXTERN $paramString";
                            break;
                        case XSAVE:
                            $mnemonic = "XSAVE";
                            $paramString = ($indirection?'@':'') . "$param0 ; EXTERN $paramString";
                            break;
                        case XLOAD:
                            $mnemonic = "XLOAD";
                            $paramString = ($indirection?'@':'') . "$param0 ; EXTERN $paramString";
                            break;
                        case XMES:
                            $mnemonic = "XMES";
                            $offset = $param0 + 256 * $param2;
                            $xmessage = getXmessageByOffset($offset, $machineName);
                            $paramString = " \"$xmessage\" ; EXTERN $paramString [Offset: $offset]";
                            break;
                        case XPART:
                            $mnemonic = "XPART";
                            $paramString = ($indirection?'@':'') . "$param0 ; EXTERN $paramString";
                            break;
                        case XBEEP: 
                            $mnemonic = "XBEEP";                           
                            $paramString = ($indirection?'@':'') . "$param0 $param2 ; EXTERN $paramString";
                            break;
                        case XSPLITSCR:
                            $mnemonic = "XSPLITSCR";
                            $paramString = ($indirection?'@':'') . "$param0 ; EXTERN $paramString";
                            break;
                        case XUNDONE:
                            $mnemonic = "XUNDONE";
                            $paramString = "; EXTERN $paramString";
                            break;
                        case XNEXTCLS:
                            $mnemonic = "XNEXTCLS";
                            $paramString = "; EXTERN $paramString";
                            break;
                        case XNEXTRESET:
                            $mnemonic = "XNEXTRESET";
                            $paramString = "; EXTERN $paramString";
                            break;
                        case XSPEED:
                            $mnemonic = "XSPEED";
                            $paramString = ($indirection?'@':'') . "$param0 ; EXTERN $paramString";
                            break;                   
                    }
                    
                }               
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
