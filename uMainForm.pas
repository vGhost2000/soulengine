unit uMainForm;

interface

{$I 'sDef.inc'}

{.$DEFINE LOAD_DS}

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, EncdDecd, MD5, Utils, NxGrid, php4delphi, core
  {$IFDEF VS_EDITOR}
  , uPHPCatButtons, uVSEditor
  {$ENDIF}

  ;

function Base64_Decode(cStr: ansistring): ansistring;
function Base64_Encode(cStr: ansistring): ansistring;

type
  T__mainForm = class(TForm)
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

uses uPHPMod;

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



procedure T__mainForm.FormCreate(Sender: TObject);
const
  php5ts_dll_md5: ansistring = 'php5ts_dll_md5000000000000000000';
var
  f, s: string;
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
  if (php5ts_dll_md5 <> 'php5ts_dll_md500000000000000' + core.four_zero_str)
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

  if not core.loadEngine() then begin
    APPLICATION.Terminate;
    exit;
  end;
  // запускаем или строим и запускаем core.phar
  if not core.buildFrameWork(PHPEngine, uPHPMod.phpMOD.psvPHP) then begin
    APPLICATION.Terminate;
    exit;
  end;
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
