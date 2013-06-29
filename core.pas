unit core;

{$ifdef fpc}
{$mode objfpc}{$H+}
{$endif}

{$I 'sDef.inc'}

interface

uses
  Classes, SysUtils, Forms, php4delphi, zendAPI, phpAPI, PHPTypes,
  regGui, guiComponents, guiForms, guiProperties, dsUtils,
  uPHPMod, WinApi.Windows, MD5, uPhpEvents
    {$IFDEF SHOW_DEBUG_MESSAGES}
      , Vcl.Dialogs
    {$ENDIF}
    {$IFDEF ADD_CHROMIUM}
      , guiChromium
    {$ENDIF}
  ;

var
  myPHPEngine: TPHPEngine;
  mypsvPHP: TpsvPHP;

const
  program_type:   ansistring = 'THIS_IS_IDE_TYPE_Hk8Geb3_3He0000';
  // а то оптимизатор, СЦУКО!, конкатенирующиеся inline константы склеивает в одну
  four_zero_str:  ansistring = '0000';


function getPsvPHP(): TpsvPHP;
procedure core_Init(aPHPEngine: TPHPEngine = nil; aPsvPHP: TpsvPHP = nil);
function buildFrameWork(aPHPEngine: TPHPEngine = nil; aPsvPHP: TpsvPHP = nil): boolean;
function loadEngine(): boolean;


implementation

function getPsvPHP(): TpsvPHP;
begin
  Result := mypsvPHP;
end;

procedure core_Init(aPHPEngine: TPHPEngine = nil; aPsvPHP: TpsvPHP = nil);
begin
  regGui.registerGui();

  if aPHPEngine = nil then
    myPHPEngine := TPHPEngine.Create(Application)
  else
    myPHPEngine := aPHPEngine;

  if aPsvPHP = nil then
    mypsvPHP := TpsvPHP.Create(Application)
  else
    mypsvPHP := aPsvPHP;

  InitializeEventSystem(myPHPEngine);
  InitializeGuiComponents(myPHPEngine);
  InitializeGuiForms(myPHPEngine);
  InitializeGuiProperties(myPHPEngine);

  {$IFDEF ADD_CHROMIUM}
  InitializeGuiChromium(myPHPEngine);
  {$ENDIF}
  InitializeDsUtils(myPHPEngine);

  myPHPEngine.StartupEngine;
end;


function loadEngine(): boolean;
const
  php_ini_md5:          ansistring = 'php_ini_md5000000000000000000000';
begin
  result := false;
  // инициализируем пхп
  if (ParamStr(2) = '-errors') then
  begin
    PHPEngine.HandleErrors := True;
  end
  else
    {$IFDEF NO_DEBUG}
      PHPEngine.HandleErrors := False;
    {$ELSE}
      PHPEngine.HandleErrors := True;
    {$ENDIF}

  PHPEngine.DLLFolder := uPHPMod.progDir;
  PHPEngine.IniPath   := uPHPMod.getIniLocation(uPHPMod.progDir);

  if (
      // если это IDE и есть md5 от php.ini - ошибка
      (php_ini_md5 <> 'php_ini_md500000000000000000' + four_zero_str) AND
      (program_type = 'THIS_IS_IDE_TYPE_Hk8Geb3_3He' + four_zero_str)
    ) OR (
      // если это НЕ IDE и нету md5 от php.ini - ошибка
      (php_ini_md5 = 'php_ini_md500000000000000000' + four_zero_str) AND
      (program_type <> 'THIS_IS_IDE_TYPE_Hk8Geb3_3He' + four_zero_str)
    ) OR (
      // если это есть md5 от php.ini и оно не совпадает - ошибка
      (php_ini_md5 <> 'php_ini_md500000000000000000' + four_zero_str) AND
      (php_ini_md5 <> LowerCase(xMD5_File(PHPEngine.IniPath)))
    )
  then begin
    MessageBox(0, 'The program is corrupted, try reinstalling it.', 'Fatal error', mb_Ok or MB_ICONERROR);
    exit;
  end;

  core_Init(PHPEngine, uPHPMod.phpMOD.psvPHP);
  addVar('progDir',   uPHPMod.progDir);
  addVar('moduleDir', uPHPMod.moduleDir);
  addVar('engineDir', uPHPMod.engineDir);

  {$IFDEF NO_DEBUG}
    uPHPMod.phpMOD.RunCode('<?php define("vGDEBUG", false); ?>');
  {$ELSE}
    uPHPMod.phpMOD.RunCode('<?php define("vGDEBUG", true); ?>');
  {$ENDIF}

  result := true;
end;


function buildFrameWork(aPHPEngine: TPHPEngine = nil; aPsvPHP: TpsvPHP = nil): boolean;
const
  core_phar_md5:          ansistring = 'core_phar_md50000000000000000000';
  modules_phar_md5:       ansistring = 'modules_phar_md50000000000000000';
  main_program_phar_md5:  ansistring = 'main_program_phar_md500000000000';
begin
  result := false;

  if not FileExists(uPHPMod.progDir + 'core.phar') then begin
    // Если файл core.phar не существует и пристутствует нормальная сигнатура (не IDE) то ошибка
    if program_type <> 'THIS_IS_IDE_TYPE_Hk8Geb3_3He' + four_zero_str then begin
      MessageBox(0, 'The program is corrupted, try reinstalling it.', 'Fatal error', mb_Ok or MB_ICONERROR);
      exit;
    end;

    if not FileExists(uPHPMod.engineDir + 'coreBuilder.php') then begin
      MessageBox(0, 'Core archive builder script not found.', 'Fatal error', mb_Ok or MB_ICONERROR);
      exit;
    end;

    uPHPMod.phpMOD.RunFile(uPHPMod.engineDir + 'coreBuilder.php');
    uPHPMod.phpMOD.RunCode('<?php CoreBuilder::buildFrameWork(); ?>');
  end;

  if not FileExists(uPHPMod.progDir + 'core.phar') then begin
    MessageBox(0, 'Core archive builder script failed build archive.', 'Fatal error', mb_Ok or MB_ICONERROR);
    exit;
  end;

  {$IFNDEF NO_DEBUG}
    if program_type = 'THIS_IS_IDE_TYPE_Hk8Geb3_3He' + four_zero_str then begin
      uPHPMod.phpMOD.RunCode('<?php if (!class_exists("CoreBuilder")) {' +
        'require_once("phar://core.phar/coreBuilder.php");} ' +
        'CoreBuilder::buildFrameWork(true); ?>'
      );
    end;
  {$ENDIF}


  // чекнем контрольную сумму архива с core скриптами
  if (core_phar_md5 <> 'core_phar_md5000000000000000' + four_zero_str)
    AND (core_phar_md5 <> LowerCase(xMD5_File(uPHPMod.progDir + 'core.phar')))
  then begin
    {$IFDEF SHOW_DEBUG_MESSAGES}
      showmessage('Wrong core.phar, APPLICATION.Terminate');
    {$ENDIF}
    exit;
  end;

  uPHPMod.phpMOD.RunCode('<?php Phar::loadPhar($GLOBALS["progDir"] . "core.phar", "core.phar");' +
    ' require_once("phar://core.phar/include.php"); ?>'
  );

  if FileExists(uPHPMod.progDir + 'modules.phar') then begin
    // чекнем контрольную сумму архива со скриптами дополнительных модулей прокта
    if (modules_phar_md5 <> 'modules_phar_md5000000000000' + four_zero_str)
      AND (modules_phar_md5 <> LowerCase(xMD5_File(uPHPMod.progDir + 'modules.phar')))
    then begin
      {$IFDEF SHOW_DEBUG_MESSAGES}
        showmessage('Wrong modules.phar, APPLICATION.Terminate');
      {$ENDIF}
      exit;
    end;
    uPHPMod.phpMOD.RunCode('<?php require_once("phar://" . $GLOBALS["progDir"] . "modules.phar/include.php"); ?>');
  end;

  if FileExists(uPHPMod.progDir + 'main_program.phar') AND
    // выполняем файл программы только если пристутствует подпись
    (program_type <> 'THIS_IS_IDE_TYPE_Hk8Geb3_3He' + four_zero_str)
  then begin
    // чекнем контрольную сумму архива со скриптами пользовательской программы
    if (main_program_phar_md5 <> 'main_program_phar_md50000000' + four_zero_str)
      AND (main_program_phar_md5 <> LowerCase(xMD5_File(uPHPMod.progDir + 'main_program.phar')))
    then begin
      {$IFDEF SHOW_DEBUG_MESSAGES}
        showmessage('Wrong main_program.phar, APPLICATION.Terminate');
      {$ENDIF}
      exit;
    end;
    showmessage('start prog');
    uPHPMod.phpMOD.RunCode('<?php Phar::loadPhar($GLOBALS["progDir"] . "main_program.phar", "main_program.phar");' +
      'require_once("phar://main_program.phar/include.php"); ?>'
    );
    result := true;
    exit;
  end else if not FileExists(uPHPMod.progDir + 'system.phar') then
    uPHPMod.phpMOD.RunCode('<?php CoreBuilder::buildSystemIDE(); ?>');

  // если присутствует подпись нормальной программы то это не IDE, тут что то не так, выдаём ошибку
  if program_type <> 'THIS_IS_IDE_TYPE_Hk8Geb3_3He' + four_zero_str then begin
    MessageBox(0, 'The program is corrupted, try reinstalling it.', 'Fatal error', mb_Ok or MB_ICONERROR);
    exit;
  end;

  if not FileExists(uPHPMod.progDir + 'system.phar') then begin
    MessageBox(0, 'SystemIDE archive builder script failed build archive.', 'Fatal error', mb_Ok or MB_ICONERROR);
    exit;
  end;

  {$IFNDEF NO_DEBUG}
    uPHPMod.phpMOD.RunCode('<?php CoreBuilder::buildSystemIDE(true); ?>');
  {$ELSE}
    if 'system_phar_md500000000000000000' <> xMD5_File(uPHPMod.progDir + 'system.phar') then begin
      {$IFDEF SHOW_DEBUG_MESSAGES}
        showmessage('Wrong system.phar, APPLICATION.Terminate');
      {$ENDIF}
      exit;
    end;
  {$ENDIF}

  uPHPMod.phpMOD.RunCode('<?php Phar::loadPhar($GLOBALS["progDir"] . "system.phar", "system.phar");' +
    ' require_once("phar://system.phar/include.pse"); ?>'
  );

  result := true;
end;


end.


