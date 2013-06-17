unit uMainForm;

interface

{$I 'sDef.inc'}

{.$DEFINE LOAD_DS}

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, ExeMod, EncdDecd, MD5, Utils, NxGrid, php4delphi, core
  {$IFDEF VS_EDITOR}
  , uPHPCatButtons, uVSEditor
  {$ENDIF}

  ;

function Base64_Decode(cStr: ansistring): ansistring;
function Base64_Encode(cStr: ansistring): ansistring;

type
  T__mainForm = class(TForm)
    procedure FormActivate(Sender: TObject);
    procedure FormCreate(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
    procedure WMHotKey(var Msg: TMessage); message WM_HOTKEY;
    procedure ReceiveMessage(var Msg: TMessage); message WM_COPYDATA;
  end;
const
  disclaimer: ansistring = #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 +
    '		    WARNING!!! WARNING!!! WARNING!!!         ' + #13 +
    '		    WARNING!!! WARNING!!! WARNING!!!         ' + #13 +
    '		    WARNING!!! WARNING!!! WARNING!!!         ' + #13 +
    '		    WARNING!!! WARNING!!! WARNING!!!         ' + #13 +
    #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 +
    '		    ----------------- FOR ANTI-VIRUS Analysts ----------------         ' + #13 + #13 +

    '			This application consists of several parts - it is done         ' + #13 +
    '			with the help of technology php2exe (analog py2exe).         ' + #13 +
    '			These real source code (bcompiler byte-code + php code)         ' + #13 +
    '			attached to the end of this exe file and start with a line:         ' + #13 + #13 +

    '			"lalalalal напиши меня"         ' + #13 + #13 +

    '		    ----------------- FOR ANTI-VIRUS Analysts END ----------------         ' + #13 +
    #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 +
    #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13 + #13
  ;

var
  __mainForm: T__mainForm;
  selfScript: string = '';
  selfMD5Hash: string;
  selfEnabled: boolean = False;
  dllPHPPath: string = '';

  selfModules: TStringList;
  selfModulesMD5: TStringList;
  selfModulesHash: string;

  selfPHP5tsMD5: string;
  selfPHP5tsSize: integer;

implementation

uses uMain, uPHPMod;

{$R *.dfm}


function Base64_Decode(cStr: ansistring): ansistring;
const
  Base64Table =
    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

var
  ResStr: ansistring;

  DecStr: ansistring;
  RecodeLine: array[1..76] of byte;
  f1: word;
  l: integer;
begin


  Result := DecodeString(cStr);
  exit;
  l := length(cStr);
  ResStr := '';
  for f1 := 1 to l do
    if cStr[f1] = '=' then
      RecodeLine[f1] := 0
    else
      RecodeLine[f1] := pos(cStr[f1], Base64Table) - 1;
  f1 := 1;
  while f1 < length(cStr) do
  begin
    DecStr := chr(byte(RecodeLine[f1] shl 2) + RecodeLine[f1 + 1] shr 4) +
      chr(byte(RecodeLine[f1 + 1] shl 4) + RecodeLine[f1 + 2] shr 2) +
      chr(byte(RecodeLine[f1 + 2] shl 6) + RecodeLine[f1 + 3]);
    ResStr := ResStr + DecStr;
    Inc(f1, 4);
  end;
  Result := ResStr;
end;

function Base64_Encode(cStr: ansistring): ansistring;
begin
  Result := EncodeString(cStr);
  __mainForm.BringToFront;
end;


procedure T__mainForm.FormActivate(Sender: TObject);
var
  f, s: string;
begin
  if appShow then
    exit;
  appShow := True;
  exit;
  {$IFDEF LOAD_DS}
  //f := ExtractFilePath(ParamStr(0)) + 'system\include.pse';
  {$ELSE}
  //f := ParamStr(1);
  {$ENDIF}
  f := ExtractFilePath(ParamStr(0)) + 'system\include.pse';
  if not FileExists(f) then f := ParamStr(1);

  if selfEnabled then
  begin
    {$IFDEF SECURITY_ON}
    if (xMD5(selfScript) = selfMD5Hash) then
    {$ENDIF}
    begin
      __fMain.Button1.Destroy;
      __fMain.MainMenu.Destroy;
      __fMain.b_Restart.Destroy;
      __fMain.b_Run.Destroy;
      __fMain.Memo1.Destroy;
      __fMain.Width := 0;
      __fMain.Height := 0;
      __fMain.BorderStyle := bsNone;


      phpMOD.RunCode(selfScript);
      selfEnabled := True;

      appShow := True;
      //__fMain.Destroy;
    end
    {$IFDEF SECURITY_ON}{$IFDEF SHOW_DEBUG_MESSAGES}
      else showmessage('xMD5(selfScript) != selfMD5Hash (wrong md5)' + #13
        + xMD5(selfScript) + #13 + selfMD5Hash
      );
      //showmessage(selfScript);
    {$ENDIF}{$ENDIF}
    ;
  end
  else if ExtractFileExt(f) = '.pse' then
  begin
    s := File2String(f);
    phpMOD.RunCode(s);
  end
  else if ParamStr(1) <> '-run' then
  begin
    uPHPMod.SetAsMainForm(__fMain);
    Application.ShowMainForm := True;
    Application.MainFormOnTaskBar := True;
  end
  else
    phpMOD.RunFile(ParamStr(2));

  appShow := True;
end;

procedure T__mainForm.FormCreate(Sender: TObject);
const
  core_phar_md5:  ansistring = 'core_phar_md50000000000000000000';
  php5ts_dll_md5: ansistring = 'php5ts_dll_md5000000000000000000';
var
  f, s: string;
  EM: TExeStream;
  modules : ansistring;
begin
  Self.Left := -999;
  // определяем некоторые везде ссущие переменные :)
  uPHPMod.progDir := ExtractFilePath(Application.ExeName);
  uPHPMod.moduleDir := uPHPMod.progDir + 'ext\';
  if DirectoryExists(uPHPMod.progDir + 'core\') then
    uPHPMod.engineDir := uPHPMod.progDir + 'core\'
  else
    uPHPMod.engineDir := uPHPMod.progDir + 'engine\';

  if not FileExists(uPHPMod.progDir + 'php5ts.dll') then begin
    MessageBox(0, 'PHP engine php5ts.dll not found.', 'Fatal error', mb_Ok or MB_ICONERROR);
    APPLICATION.Terminate;
    exit;
  end;

  // чекнем контрольную сумму движка пхп
  if (php5ts_dll_md5 <> 'php5ts' + '_dll_md50000' + '00000000000000')
    AND (php5ts_dll_md5 <> LowerCase(xMD5_File(uPHPMod.progDir + 'php5ts.dll')))
  then begin
    {$IFDEF SHOW_DEBUG_MESSAGES}
      showmessage('Wrong php5ts_dll_md5, APPLICATION.Terminate');
    {$ENDIF}
    APPLICATION.Terminate;
    exit;
  end;

  Application.CreateForm(TphpMOD, phpMOD);
  {$IFDEF VS_EDITOR}
    Application.CreateForm(TphpVSEditor, phpVSEditor);
    Application.CreateForm(TPHPCatButtons, PHPCatButtons);
  {$ENDIF}

  core.loadEngine();
  // запускаем или строим и запускаем core.phar
  if not core.buildFrameWork(PHPEngine, uPHPMod.phpMOD.psvPHP) then begin
    APPLICATION.Terminate;
    exit;
  end;


  f := uPHPMod.progDir + 'system\include.pse';
  if FileExists(f) then begin
    s := File2String(f);
    phpMOD.RunCode(s);
  end;

  APPLICATION.Terminate;
 //////////////////////////////////////////////////////////////////////////////////////
  exit;



//phpMOD.RunCode('<?php gui_message("12312312"); ?>');
{  if not FileExists(uPHPMod.progDir + 'core.phar') then begin
    MessageBox(0, 'PHP engine php5ts.dll not found.', 'Fatal error', mb_Ok or MB_ICONERROR);
    APPLICATION.Terminate;
    exit;
  end;
 }
  APPLICATION.Terminate;
 //////////////////////////////////////////////////////////////////////////////////////
  exit;
  Self.Left := -999;
  f := ExtractFilePath(ParamStr(0)) + 'system\include.pse';
  if not FileExists(f) then f := ParamStr(1);

  selfScript := '';
  EM := TExeStream.Create(ParamStr(0));

  progDir := ExtractFilePath(Application.ExeName);
  moduleDir := progDir + 'ext\';
  engineDir := progDir + 'engine\';
  if DirectoryExists(progDir + 'core\') then
    engineDir := progDir + 'core\';

  selfScript := EM.ExtractToString('$PHPSOULENGINE\inc.php');
  selfMD5Hash := EM.ExtractToString('$PHPSOULENGINE\inc.php.hash');
  if (selfScript <> '') then
  begin
    //selfScript := myDecode(Base64_Decode(selfScript));
    selfModulesHash := EM.ExtractToString('$PHPSOULENGINE\mods.hash');
    modules         := EM.ExtractToString('$PHPSOULENGINE\mods');
    //{
    if (sizeof(modules) <> 4) and (xMD5(modules) <>
      selfModulesHash) then
    begin
      {$IFDEF SHOW_DEBUG_MESSAGES}
        showmessage('$PHPSOULENGINE\mods wrong md5');
      {$ENDIF}
      selfMD5Hash := '';
      selfScript := '';
      selfEnabled := True;
      exit;
    end;
     // }

    selfModules := TStringList.Create;
    selfModules.Text :=
      StringReplace(EM.ExtractToString('$PHPSOULENGINE\mods'), ',',
      #13, [rfReplaceAll]);

    selfModulesMD5 := TStringList.Create;
    selfModulesMD5.Text :=
      StringReplace(EM.ExtractToString('$PHPSOULENGINE\mods_m'),
      ',', #13, [rfReplaceAll]);


    selfPHP5tsMD5 := EM.ExtractToString('$PHPSOULENGINE\phpts.hash');
    selfPHP5tsSize := StrToIntDef(EM.ExtractToString('$PHPSOULENGINE\phpts.size'), -1);

    selfEnabled := True;
  end;


  if (ExtractFileExt(f) = '.pse') and (selfScript = '') then
  begin
    if Pos(':', F) > 0 then
      progDir := ExtractFilePath(f)
    else
      progDir := progDir + ExtractFilePath(f);
  end
  else if selfScript <> '' then
    progDir := ExtractFilePath(ParamStr(0))
  else if f <> '' then
    progDir := ExtractFilePath(f);
end;

procedure T__mainForm.ReceiveMessage(var Msg: TMessage);
var
  pcd: PCopyDataStruct;
  s: ansistring;
begin
  pcd := PCopyDataStruct(Msg.LParam);
  s := PChar(pcd.lpData);

  phpMOD.RunCode('Receiver::event(' + IntToStr(Msg.WParam) + ',''' +
    AddSlashes(s) + ''');');
end;

procedure T__mainForm.WMHotKey(var Msg: TMessage);
var
  idHotKey: integer;
  fuModifiers: word;
  uVirtKey: word;
begin
  idHotkey := Msg.wParam;
  fuModifiers := LOWORD(Msg.lParam);
  uVirtKey := HIWORD(Msg.lParam);

  phpMOD.RunCode('HotKey::event(' + IntToStr(fuModifiers) + ',' +
    IntToStr(uVirtKey) + ');');

  inherited;
end;


end.
