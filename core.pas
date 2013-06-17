unit core;

{$ifdef fpc}
{$mode objfpc}{$H+}
{$endif}

interface

uses
  Classes, SysUtils, Forms, php4delphi, zendAPI, phpAPI, PHPTypes,
  regGui, guiComponents, guiForms, guiProperties, dsUtils,
  uPHPMod, WinApi.Windows,
  {$IFDEF ADD_CHROMIUM}
  guiChromium,
  {$ENDIF}

  uPhpEvents;

var
  myPHPEngine: TPHPEngine;
  mypsvPHP: TpsvPHP;

function getPsvPHP(): TpsvPHP;
procedure core_Init(aPHPEngine: TPHPEngine = nil; aPsvPHP: TpsvPHP = nil);
function buildFrameWork(aPHPEngine: TPHPEngine = nil; aPsvPHP: TpsvPHP = nil): boolean;
procedure loadEngine();


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


procedure loadEngine();
begin
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

  core_Init(PHPEngine, uPHPMod.phpMOD.psvPHP);
  addVar('progDir',   uPHPMod.progDir);
  addVar('moduleDir', uPHPMod.moduleDir);
  addVar('engineDir', uPHPMod.engineDir);
end;


function buildFrameWork(aPHPEngine: TPHPEngine = nil; aPsvPHP: TpsvPHP = nil): boolean;
begin
  result := false;

  if not FileExists(uPHPMod.progDir + 'core.phar') then begin
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

  uPHPMod.phpMOD.RunCode('<?php require_once("phar://" . $GLOBALS["progDir"] . "core.phar/include.php"); ?>');
  result := true;
end;


end.


