
<?php

// include_once("exportSCE.php"); // Not implemented yet
include_once("exportDSF.php");

define('VERSION','1.0');


// This DAAD decompiler is made for v2.0+ of DAAD compiler, thus, will support ony games made 
// with DC 2.0+ or DRC, but won't work with games made with older version (basically, early
// Aventuras AD games).

// This decompiler is based in unDAAAD, by José Manuel Ferrer Ortiz, and Uto

// Original work copyright (C) 2008-2010, 2013 José Manuel Ferrer Ortiz
// Fixes and completion copyright (C) Uto (2015-2019)
// DRC and Maluva stuff, (C) Uto 2023 

// License: GNU GPL v3.0 (see LICENSE file for details)

// Header of the DDB files

// POSITION  LENGTH    CONTAINS    
// 0         1 byte    DAAD version number
// 1         1 byte    High nibble: target machine | Low nibble: target language
// 2         1 byte    Always contains 95, not identified
// 3         1 byte    Number of object descriptions
// 4         1 byte    Number of location descriptions
// 5         1 byte    Number of user messages
// 6         1 byte    Number of system messages
// 7         1 byte    Number of processes
// 8         2 bytes   Compressed text position
// 10        2 bytes   Process list position
// 12        2 bytes   Objects lookup list position
// 14        2 bytes   Locations lookup list position
// 16        2 bytes   User messages lookup list position
// 18        2 bytes   System messages lookup list position
// 20        2 bytes   Connections lookup list position
// 22        2 bytes   Vocabulary  
// 24        2 bytes   Objects "initialy at" list position
// 26        2 bytes   Object names positions
// 28        2 bytes   Object weight and container/wearable attributes
// 30        2 bytes   Extra object attributes 
// 32        2 bytes   File length 

// For DAAD V3:

// 30        2 bytes   Extra object attributes 
// 32        1 byte    Number of messages in secondary message table (MTX2)
// 33        2 bytes   Secondary user messages lookup list position
// 35        2 bytes   File length 


// GLOBAL CONTANTS

  
// Vocabulary word types
$wordTypes = array("verb", "adverb", "noun", "adjective", "preposition",
                           "conjugation", "pronoun");
  

                        
    

    

$terminatorOpcodes = array(22, 23,103, 116,117,108);  //DONE/OK/NOTDONE/SKIP/RESTART/REDO   


$tokens_to_iso8859_15 = array(
    0,   1,   2,   3,   4,   5,   6,   7,   8,   9,  //   0 -   9
    10,  11,  12,  13,  14,  15, 170, 161, 191, 171,  //  10 -  19
    187, 225, 233, 237, 243, 250, 241, 209, 231, 199,  //  20 -  29
    252, 220,  ord('_'),  33,  34,  35,  36,  37,  38,  39,  //  30 -  39
    40,  41,  42,  43,  44,  45,  46,  47,  48,  49,  //  40 -  49
    50,  51,  52,  53,  54,  55,  56,  57,  58,  59,  //  50 -  59
    60,  61,  62,  63,  64,  65,  66,  67,  68,  69,  //  60 -  69
    70,  71,  72,  73,  74,  75,  76,  77,  78,  79,  //  70 -  79
    80,  81,  82,  83,  84,  85,  86,  87,  88,  89,  //  80 -  89
    90,  91,  92,  93,  94,  95,  96,  97,  98,  99,  //  90 -  99
    100, 101, 102, 103, 104, 105, 106, 107, 108, 109,  // 100 - 109
    110, 111, 112, 113, 114, 115, 116, 117, 118, 119,  // 110 - 119
    120, 121, 122, 123, 124, 125, 126, 127             // 120 - 127
);

$daad_to_iso8859_15 = array(
    255, 254, 253, 252, 251, 250, 249, 248, 247, 246,  //   0 -   9
    245, 244, 243, 242, 241, 240, 239, 238, 237, 236,  //  10 -  19
    235, 234, 233, 232, 231, 230, 229, 228, 227, 226,  //  20 -  29
    225, 224, 223, 222, 221, 220, 219, 218, 217, 216,  //  30 -  39
    215, 214, 213, 212, 211, 210, 209, 208, 207, 206,  //  40 -  49
    205, 204, 203, 202, 201, 200, 199, 198, 197, 196,  //  50 -  59
    195, 194, 193, 192, 191, 190, 189, 188, 187, 186,  //  60 -  69
    185, 184, 183, 182, 181, 180, 179, 178, 177, 176,  //  70 -  79
    175, 174, 173, 172, 171, 170, 169, 168, 167, 166,  //  80 -  89
    165, 164, 163, 162, 161, 160, 159, 158, 157, 156,  //  90 -  99
    155, 154, 153, 152, 151, 150, 149, 148, 147, 146,  // 100 - 109
    145, 144, 143, 142, 141, 140, 139, 138, 137, 136,  // 110 - 119
    135, 134, 133, 132, 131, 130, 129, 128, 127, 126,  // 120 - 129
    125, 124, 123, 122, 121, 120, 119, 118, 117, 116,  // 130 - 139
    115, 114, 113, 112, 111, 110, 109, 108, 107, 106,  // 140 - 149
    105, 104, 103, 102, 101, 100,  99,  98,  97,  96,  // 150 - 159
    95,  94,  93,  92,  91,  90,  89,  88,  87,  86,  // 160 - 169
    85,  84,  83,  82,  81,  80,  79,  78,  77,  76,  // 170 - 179
    75,  74,  73,  72,  71,  70,  69,  68,  67,  66,  // 180 - 189
    65,  64,  63,  62,  61,  60,  59,  58,  57,  56,  // 190 - 199
    55,  54,  53,  52,  51,  50,  49,  48,  47,  46,  // 200 - 209
    45,  44,  43,  42,  41,  40,  39,  38,  37,  36,  // 210 - 219
    35,  34,  33,  32, 220, 252, 199, 231, 209, 241,  // 220 - 229
    250, 243, 237, 233, 225, 187, 171, 191, 161, 170,  // 230 - 239
    15,  14,  13,  12,  11,  10,   9,   8,   7,   6,  // 240 - 249
    5,   4,   3,   2,   1,   0                       // 250 - 255
);


$condacts = array(
      array(1,'AT       '), //   0 $00
      array(1,'NOTAT    '), //   1 $01
      array(1,'ATGT     '), //   2 $$02
      array(1,'ATLT     '), //   3 $03
      array(1,'PRESENT  '), //   4 $04
      array(1,'ABSENT   '), //   5 $05
      array(1,'WORN     '), //   6 $06
      array(1,'NOTWORN  '), //   7 $07
      array(1,'CARRIED  '), //   8 $08
      array(1,'NOTCARR  '), //   9 $09
      array(1,'CHANCE   '), //  10 $0A
      array(1,'ZERO     '), //  11 $0B
      array(1,'NOTZERO  '), //  12 $0C
      array(2,'EQ       '), //  13 $0D
      array(2,'GT       '), //  14 $0E
      array(2,'LT       '), //  15 $0F
      array(1,'ADJECT1  '), //  16 $10
      array(1,'ADVERB   '), //  17 $11
      array(2,'SFX      '), //  18 $12
      array(1,'DESC     '), //  19 $13
      array(0,'QUIT     '), //  20 $14
      array(0,'END      '), //  21 $15
      array(0,'DONE     '), //  22 $16
      array(0,'OK       '), //  23 $17
      array(0,'ANYKEY   '), //  24 $18
      array(1,'SAVE     '), //  25 $19
      array(1,'LOAD     '), //  26 $1A
      array(1,'DPRINT   '), //  27 * $1B
      array(1,'DISPLAY  '), //  28 * $1C
      array(0,'CLS      '), //  29 $1D
      array(0,'DROPALL  '), //  30 $1E
      array(0,'AUTOG    '), //  31 $1F
      array(0,'AUTOD    '), //  32 $20
      array(0,'AUTOW    '), //  33 $21
      array(0,'AUTOR    '), //  34 $22
      array(1,'PAUSE    '), //  35 $23
      array(2,'SYNONYM  '), //  36 * $24
      array(1,'GOTO     '), //  37 $25
      array(1,'MESSAGE  '), //  38 $26
      array(1,'REMOVE   '), //  39 $27
      array(1,'GET      '), //  40 $28
      array(1,'DROP     '), //  41 $29
      array(1,'WEAR     '), //  42 $2A
      array(1,'DESTROY  '), //  43 $2B
      array(1,'CREATE   '), //  44 $2C
      array(2,'SWAP     '), //  45 $2D
      array(2,'PLACE    '), //  46 $2E
      array(1,'SET      '), //  47 $2F
      array(1,'CLEAR    '), //  48 $30
      array(2,'PLUS     '), //  49 $31
      array(2,'MINUS    '), //  50 $32
      array(2,'LET      '), //  51 $33
      array(0,'NEWLINE  '), //  52 $34
      array(1,'PRINT    '), //  53 $35
      array(1,'SYSMESS  '), //  54 $36
      array(2,'ISAT     '), //  55 $37
      array(1,'SETCO    '), //  56 $38 COPYOF in old games 
      array(0,'SPACE    '), //  57 $39 COPYOO in old games
      array(1,'HASAT    '), //  58 $3A COPYFO in old games
      array(1,'HASNAT   '), //  59 $3B COPYFF in old games
      array(0,'LISTOBJ  '), //  60 $3C
      array(2,'EXTERN   '), //  61 $3D
      array(0,'RAMSAVE  '), //  62 $3E
      array(1,'RAMLOAD  '), //  63 $3F
      array(2,'BEEP     '), //  64 $40
      array(1,'PAPER    '), //  65 $41
      array(1,'INK      '), //  66 $42
      array(1,'BORDER   '), //  67 $43
      array(1,'PREP     '), //  68 $44
      array(1,'NOUN2    '), //  69 $45
      array(1,'ADJECT2  '), //  70 $46
      array(2,'ADD      '), //  71 $47
      array(2,'SUB      '), //  72 $48
      array(1,'PARSE    '), //  73 $49
      array(1,'LISTAT   '), //  74 $4A
      array(1,'PROCESS  '), //  75 $4B
      array(2,'SAME     '), //  76 $4C
      array(1,'MES      '), //  77 $4D
      array(1,'WINDOW   '), //  78 $4E
      array(2,'NOTEQ    '), //  79 $4F
      array(2,'NOTSAME  '), //  80 $50
      array(1,'MODE     '), //  81 $51
      array(2,'WINAT    '), //  82 $52
      array(2,'TIME     '), //  83 $53
      array(1,'PICTURE  '), //  84 $54
      array(1,'DOALL    '), //  85 $55
      array(1,'MOUSE    '), //  86 $56
      array(2,'GFX      '), //  87 $57
      array(2,'ISNOTAT  '), //  88 $58
      array(2,'WEIGH    '), //  89 $59
      array(2,'PUTIN    '), //  90 $5A
      array(2,'TAKEOUT  '), //  91 $5B
      array(0,'NEWTEXT  '), //  92 $5C
      array(2,'ABILITY  '), //  93 $5D
      array(1,'WEIGHT   '), //  94 $5E
      array(1,'RANDOM   '), //  95 $5F
      array(2,'INPUT    '), //  96 $60
      array(0,'SAVEAT   '), //  97 $61
      array(0,'BACKAT   '), //  98 $62
      array(2,'PRINTAT  '), //  99 $63
      array(0,'WHATO    '), // 100 $64
      array(1,'CALL     '), // 101 $65
      array(1,'PUTO     '), // 102 $66
      array(0,'NOTDONE  '), // 103 $67
      array(1,'AUTOP    '), // 104 $68
      array(1,'AUTOT    '), // 105 $69
      array(1,'MOVE     '), // 106 $6A
      array(2,'WINSIZE  '), // 107 $6B
      array(0,'REDO     '), // 108 $6C
      array(0,'CENTRE   '), // 109 $6D
      array(1,'EXIT     '), // 110 $6E
      array(0,'INKEY    '), // 111 $6F
      array(2,'BIGGER   '), // 112 $70
      array(2,'SMALLER  '), // 113 $71
      array(0,'ISDONE   '), // 114 $72
      array(0,'ISNDONE  '), // 115 $73
      array(1,'SKIP     '), // 116 $74
      array(0,'RESTART  '), // 117 $75
      array(1,'TAB      '), // 118 $76
      array(2,'COPYOF   '), // 119 $77
      array(0,'dumb     '), // 120 $78 (according DAAD manual, internal) // PREFIX for DAAD V3
      array(2,'COPYOO   '), // 121 $79 
      array(0,'dumb     '), // 122 $7A (according DAAD manual, internal) // SETP2 for DAAD V3
      array(2,'COPYFO   '), // 123 $7B
      array(0,'dumb     '), // 124 $7C (according DAAD manual, internal) // SETP3 for DAAD V3
      array(2,'COPYFF   '), // 125 $7D
      array(2,'COPYBF   '), // 126 $7E
      array(0,'RESET    ')  // 127 $7F
 );

$prefixedCondacts = array(
      array(2,'BSET     '), //   0 $00
      array(2,'BCLEAR   '), //   1 $01
      array(2,'BTOGGLE  '), //   2 $02
      array(2,'BZERO    '), //   3 $03
      array(2,'BNOTZERO '), //  4 $04
      array(1,'SELECT   '), //  5 $05
      array(1,'OPTION   '), //  6 $06
      array(1,'CHOICE   '), //  7 $07
      array(1,'TOGGLECON'), //  8 $08
      array(1,'MES2     '), //  9 $09
); 
    
define('PREFIX_OPCODE', 120);
define('SETP2_OPCODE',  122);
define('SETP3_OPCODE',  124);
    

// GLOBAL VARS
$isLittleEndian = false;
$maluva = true;
$inlineMessages = true;
$objectIdentifiers = true;
$auto = false;
$exportFormat = 'DSF';


$dumpTokens = false;
$DDBoffset = 0;

// Sections
$HEADER = $CTL = $TOC = $VOC = $LTX =  $MTX = $MTX2 = $STX =  $OTX = $OBJ = $CON = $PRO = array();


// DDB read functions


function findDDB($data)
{
    
    for ($i=0;$i<strlen($data)-2;$i++)
    {
        $byte = ord(substr($data, $i, 1));
        if ($byte == 2)
        {
            $byte = ord(substr($data, $i+1, 1)) & 0x0F;
            if ($byte<2)
            {
                $byte = ord(substr($data, $i+2, 1));
                if ($byte == 95)
                    return $i;
            }
        }
    }
    return -1;

}

function readDDB($filename)
{
    global $DDBoffset, $auto, $dataPTR;
    $dataPTR = 0;
    $fp = fopen($filename, "rb");
    if (!$fp)
        error("Cannot open file $filename");
    $data = fread($fp, filesize($filename));
    fclose($fp);
    $DDBoffset = 0;
    if ($auto)
    {
        $DDBoffset = findDDB($data);
        if ($DDBoffset == -1)
            error("Cannot find DDB data in file $filename");
    }
    seek(0);
    return $data;
}



function readByte($verbose=false)
{
    global $data, $dataPTR;
    $byte = ord(substr($data, $dataPTR, 1));
    $dataPTR++;
    if ($verbose) echo "[RB: $byte]";
    return $byte;
}

function readWord()
{
    global $data, $dataPTR, $isLittleEndian ;
    if (!$isLittleEndian)  $word = ord(substr($data, $dataPTR, 1)) + ord(substr($data, $dataPTR+  1, 1)) * 256;
                     else  $word = ord(substr($data, $dataPTR, 1)) * 256 + ord(substr($data, $dataPTR+ 1, 1)); 
    $dataPTR+=2;
    return $word;
}

function seek($pos)
{
    global $dataPTR, $baseAddress, $DDBoffset;
    $dataPTR = $pos - $baseAddress + $DDBoffset;
}



// target functions

function isLittleEndianPlatform($machineID)
{
    $target = getTargetByMachineID($machineID);
    return (($target=='ST') || ($target=='AMIGA'));
};

function getBaseAddressByTargetName($machineName)
{
  if ($machineName=='ZX') return 0x8400; else
  if ($machineName=='MSX') return 0x100; else
  if ($machineName=='CPC') return 0x2880; else
  if ($machineName=='CP4') return 0x7080; else
  if ($machineName=='C64') return 0x3880;

  return 0;
};

function getTargetByMachineID($id)
{
  if ($id==0) return 'PC'; else
  if ($id==1) return 'ZX'; else
  if ($id==2) return 'C64'; else
  if ($id==3) return 'CPC'; else
  if ($id==4) return 'MSX'; else
  if ($id==5) return 'ST'; else
  if ($id==6) return 'AMIGA'; else
  if ($id==7) return 'PCW'; else
  if ($id==0x0E) return 'CP4'; else
  if ($id==0x0F) return 'MSX2';
};  

// Aux functions

function getMessage($opcode, $id)
{
    global $MTXData, $STXData, $LTXData, $MTX2Data;
    switch($opcode)
    {
        case 38: 
        case 77: return $MTXData[$id];
        case 54: return $STXData[$id];
        case 19; return $LTXData[$id];
        case 512 + 9 : $MTX2Data[$id];
        default: return '';                
    }

}


function getMessageAt($address)
{
  
  global 
  $daad_to_iso8859_15, $TOK;
  $message='';

  seek($address);
    do
    {
        $c = readByte();
        if ($c < 128) 
        {
          $token_id = $c ^ 0xff - 128;
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

function getTextSection($address, $num)
{
    $variable = array();
    for ($i = 0; $i < $num; $i++)
    {
        seek($address + (2 * $i));
        $currentTextAddress = readWord();
        $variable[$i] = getMessageAt($currentTextAddress);
    }
    return $variable;
}



function syntax()
{
    echo("Syntax: php undrc.php <DDB file> [-v] [-a] [-i] [-t]\n");
    echo("        -m: do not try to identify Maluva condacts\n");
    echo("        -o: do not generate automatic object identifiers\n");
    echo("        -i: do not generate inline messages\n");
    echo("        -a: try to find DDB data in the input file\n");
    echo("        -t: save .tok file with tokens for DRC\n"); 
    // echo("        -sce: generate SCE output\n"); // not implemented yet
    echo ("\n");
    echo "Notes:\n";
    echo "- Output file will have same name than input file, but with DSF extension.\n\n";
    echo "- unDRC can be used with modern DAAD games, but may not work fine with Aventuras AD games, specially with Aventura Original, Jabato and Cozumel. Use unDAAD instead.\n";
    echo "- unDRC can't decompile Spectrum 128K DAAD games, as the DDB file is not stored the same way. Also, the 'auto' option would fail to find DDB data in MSX2 games..\n";
    echo "\n\nPlease read README file at https://github.com/Utodev/unDRC for more detailed information.\n";
    echo "\n\nContact the author at @uto_dev in Twitter/X or via the DAAD Ready Telegram group (https://t.me/daadready).\n";
    

    exit(1);    
}

function error($msg)
{
    echo("Error: $msg\n");
    exit(1);
}

//-------------------------------------------------------------------------------------
// Main
echo "unDRC 1.1\n";
if ($argc<2) syntax();

for ($i=2; $i<$argc; $i++)
{
    if ($argv[$i]=='-m') $maluva = false;
    else if ($argv[$i]=='-a') $auto = true;
    else if ($argv[$i]=='-t') $dumpTokens = true;
    else if ($argv[$i]=='-i') $inlineMessages = false;
    else if ($argv[$i]=='-o') $objectIdentifiers = false;
    else error('Invalid parameter: '.$argv[$i]);
    /* Not implemented yet
    else if ($argv[$i]=='-sce') 
    {
      $exportFormat = 'SCE';
      $dumpTokens = true;
      $inlineMessages = false;
      $maluva = false;
    }
    else error("Unknown option: $argv[$i]");
    */
    
}

$filename = $argv[1];
if (!file_exists($filename))
    error("File $filename does not exist");


$data = readDDB($filename, $auto);


// Check header

$version = readByte();
if ($version<2) error("File $filename is not a valid DDB 2.0 or 3.0 file. DDB version is $version.");
$v3code = $version==3;
$machineLang = readByte();
$target = $machineLang >> 4;
$lang = $machineLang & 0x0F;

$machineName = getTargetByMachineID($target);
$isLittleEndian = isLittleEndianPlatform($target);
$baseAddress = getBaseAddressByTargetName($machineName);



$underscoreChar = readByte();
$subtarget = 0;
if ($machineName=='MSX2') $subtarget = readByte();  else if ($underscoreChar!=95) error("File $filename is not a valid DDB 2.0 file. Magic 95h not found.");

$numObjs = readByte();
$numLocs = readByte();
$numUserMessages = readByte();
$numSystemMessages = readByte();
$numProcesses = readByte();


$posTokens   = readWord();
$posProcesses = readWord();
$posObjects = readWord();
$posLocations = readWord();
$posUserMessages = readWord();
$posSystemMessages = readWord();
$posConnections = readWord();
$posVocabulary = readWord();
$posInitialyAt = readWord();
$posObjectsVocabulary = readWord();
$posObjectAttributes = readWord();
$posObjectUserAttributes = readWord();
if ($v3code)
{
  $numUser2Messages = readByte();
  $posUser2Messages = readWord();
}
$fileLength = readWord();


$HEADER = array();
$HEADER['version'] = $version;
$HEADER['machineLang'] = $machineLang;
$HEADER['target'] = $target;
$HEADER['lang'] = $lang;
$HEADER['machineName'] = $machineName;
$HEADER['isLittleEndian'] = $isLittleEndian;
$HEADER['baseAddress'] = $baseAddress;
$HEADER['underscoreChar'] = $underscoreChar;
$HEADER['subtarget'] = $subtarget;
$HEADER['numObjs'] = $numObjs;  
$HEADER['numLocs'] = $numLocs;
$HEADER['numUserMessages'] = $numUserMessages;
$HEADER['numSystemMessages'] = $numSystemMessages;
$HEADER['numProcesses'] = $numProcesses;
$HEADER['posTokens'] = $posTokens;  
$HEADER['posProcesses'] = $posProcesses;
$HEADER['posObjects'] = $posObjects;
$HEADER['posLocations'] = $posLocations;
$HEADER['posUserMessages'] = $posUserMessages;
$HEADER['posSystemMessages'] = $posSystemMessages;
$HEADER['posConnections'] = $posConnections;
$HEADER['posVocabulary'] = $posVocabulary;
$HEADER['posInitialyAt'] = $posInitialyAt;
$HEADER['posObjectsVocabulary'] = $posObjectsVocabulary;
$HEADER['posObjectAttributes'] = $posObjectAttributes;
$HEADER['posObjectUserAttributes'] = $posObjectUserAttributes;
if ($v3code)
{
  $HEADER['numUser2Messages'] = $numUser2Messages;
  $HEADER['posUser2Messages'] = $posUser2Messages;
}
$HEADER['fileLength'] = $fileLength;


// TOKENS

seek ($posTokens+1) ;  // It seems actual token table starts 1 byte above the real offset
$tokenCount = 0;
$token = '';
while ($tokenCount<128)  // There should be exactly 128 tokens
{
  $c = readByte();
  
  if ($c==0) break;
  if ($c > 127) 
  {   
    $token .=  chr($tokens_to_iso8859_15[$c & 127]);
    $TOK[$tokenCount] = str_replace('_', ' ',  $token);
    $tokenCount++;
    $token = '';
  } else $token .=  chr($tokens_to_iso8859_15[$c]);
}



//VOCABULARY

seek($posVocabulary);
while (1)
{
  $c = readByte();
  if (!$c) break;  // End of vocabulary list  
  $currentWord = chr($c); 
  $c = $daad_to_iso8859_15[$c];
  $currentWord = chr($c);
  for ($i=0;$i<4;$i++) 
  { 
    $c= readByte();
    $c = $daad_to_iso8859_15[$c];
    $currentWord .= chr($c); 
  }
  $id  = readByte();
  $wordType = readByte();
  $wordTypeText = $wordTypes[$wordType];

  if (!isset($VOC[$wordType])) $VOC[$wordType] = array();
  if (!isset($VOC[$wordType][$id])) $VOC[$wordType][$id] = array();
  $VOC[$wordType][$id][]=$currentWord;
}


// The several /*TX  sections have the same format, so using a single function to decode them


$STX = getTextSection($posSystemMessages, $numSystemMessages);
$MTX = getTextSection($posUserMessages, $numUserMessages);
$LTX = getTextSection($posLocations, $numLocs);
$OTX = getTextSection($posObjects, $numObjs);
if ($v3code) $MTX2 = getTextSection($posUser2Messages, $numUser2Messages);


// CONNECTIONS

 
 for ($i = 0; $i < $numLocs; $i++)
 {
   $CON[$i] =array();
   seek($posConnections + (2 * $i));
   $currentConnectionsPosition = readWord();
   seek($currentConnectionsPosition);
   while (($c = readByte()) != 255)
     $CON[$i][$c] = readByte();
 }

 // OBJECT DATA
 for ($i = 0; $i < $numObjs; $i++)
 {
    $OBJ[$i] = array();
    // initially at
    seek($posInitialyAt + $i);
    $loc = readByte();
    
    $OBJ[$i]['initiallyAt'] = $loc;

    // Object attributes
    seek($posObjectAttributes + $i);
    // Weight
    $attr = readByte(); 
    $weigth = $attr & 0x3F;
    $OBJ[$i]['weight'] = $weigth;
    $OBJ[$i]['container'] = ($attr & 0x40) ? 1 : 0;
    $OBJ[$i]['wearable'] = ($attr & 0x80) ? 1 : 0;
    // User attributes
    seek($posObjectUserAttributes + ($i * 2));
    $userAttrs = readWord();
    $OBJ[$i]['userAttrs'] = $userAttrs;

   // noun & adjective
   seek($posObjectsVocabulary + ($i *2));
   $noun_id = readByte();
   $adject_id = readByte();
   $OBJ[$i]['noun'] = $noun_id;
   $OBJ[$i]['adjective'] = $adject_id;
 }


 // PROCESSES
 $PRO = array();
 
 for ($i=0;$i<$numProcesses;$i++)
 {

   $PRO[$i] = array();
   for ($entry = 0; ; $entry++)
   {
     $entryOffsetPosition = $posProcesses + (2 * $i);
     seek($entryOffsetPosition);
     $condactsOffset = readWord();
     $condactsOffset+=$entry*4;
     seek($condactsOffset);
     $verbCode = readByte();
     
     if ($verbCode == 0)  break; // Process end

     $PRO[$i][$entry]['verb'] = $verbCode;

     $nounCode = readByte();
     $PRO[$i][$entry]['noun'] = $nounCode;
     $condactsPos = readWord();
     seek($condactsPos); 
     // Condacts
     $condactNum = 0;
     $c = readByte();
     $PRO[$i][$entry]['condacts'] = array();
     while ($c != 255)
     {
      $secondIndirection = false;
      if ($v3code && (($c == SETP2_OPCODE) || ($c == SETP3_OPCODE)))
      {
        
        $secondIndirection = readByte();
        $c = readByte();
      }

      $prefixed = false;
      if ($v3code && ($c == PREFIX_OPCODE))
      {
        $prefixed = true;
        
        $c = readByte();
      }
       
       $PRO[$i][$entry]['condacts'][$condactNum] = array();
       $indirection = 0;
       if ($c > 127)
       {
         $c -= 128;
         $indirection = 1;
       }
       if ($c >= 128) error("Unknown condact code: [$c] at entry $entry of process $i ".($prefixed?"( prefixed)":"") ."\n");
       
       $PRO[$i][$entry]['condacts'][$condactNum]['indirection'] = $indirection;

       $opcode = $c;
       if ($prefixed) $opcode+=512; // Add 512 to opcode to mark it as prefixed 
       

       $PRO[$i][$entry]['condacts'][$condactNum]['opcode'] = $opcode;
       

       $PRO[$i][$entry]['condacts'][$condactNum]['params'] = array();
       if (!$prefixed)  $numParams = $condacts[$opcode][0];
                        else $numParams = $prefixedCondacts[$opcode-512][0];
       for ($j = 0; $j < $numParams; $j++)
       {
         $val = readByte();
         $PRO[$i][$entry]['condacts'][$condactNum]['params'][$j] = $val;
       }

       $PRO[$i][$entry]['condacts'][$condactNum]['indirection2'] = 0;
       if ($secondIndirection) 
       {
        $PRO[$i][$entry]['condacts'][$condactNum]['params'][1] = $secondIndirection;
        $PRO[$i][$entry]['condacts'][$condactNum]['indirection2'] = 1;
       }
       

       if (($maluva) && ($opcode==EXTERN_OPCODE))
       {
        if (in_array($val, array(XMES, XBEEP))) // Get the third paramteter for XMESSAGE
        {
            $val = readByte();
            $PRO[$i][$entry]['condacts'][$condactNum]['params'][2] = $val;
        }

       }


       $prefixed = false;
       $secondIndirection = false;
       $c = readByte();
       if (in_array($opcode, $terminatorOpcodes) && ($c!=255)) // If compiled with DRC, there is a chance there is no terminator after one of this codes
       {
         seek($dataPTR - 1);  // Create fake $ff and rewind file one byte
         $c = 255;
       }
       $condactNum++;
     }
   }

 }  


 
   // Dump data
   $dataOutput = array();
   $dataOutput['HEADER'] = $HEADER;
   $dataOutput['CTL'] = $CTL;
   $dataOutput['TOK'] = $TOK;
   $dataOutput['VOC'] = $VOC;
   $dataOutput['OBJ'] = $OBJ;
   $dataOutput['PRO'] = $PRO;
   $dataOutput['VOC'] = $VOC;
   $dataOutput['STX'] = $STX;
   $dataOutput['LTX'] = $LTX;
   $dataOutput['OTX'] = $OTX;
   $dataOutput['MTX'] = $MTX;
   if ($v3code) $dataOutput['MTX2'] = $MTX2;
   $dataOutput['CON'] = $CON;
   
  file_put_contents('dataOutput.txt', var_export($dataOutput, true));

   switch($exportFormat)
   {
    case 'DSF':   $output = generateDSF($dataOutput, $inlineMessages, $maluva, $dumpTokens, $objectIdentifiers); break;
    // case 'SCE':   $output = generateSCE($dataOutput, $inlineMessages, $maluva, $dumpTokens); break; // Not implemented yet
   }

   $outputFileName =  replaceExtension($filename, $exportFormat);
   if (file_exists($outputFileName)) $action = 'updated'; else $action = 'created';
   file_put_contents($outputFileName, $output);
   echo "File $outputFileName $action.\n";
