program soulEngine;

{$I 'sDef.inc'}

uses
  Forms,
  ceflib,
  Dialogs,
  SysUtils,
  uMainForm in 'uMainForm.pas' {__mainForm},
  uPHPMod in 'uPHPMod.pas' {phpMOD: TDataModule},
  uGuiScreen in 'uGuiScreen.pas',
  uApplication in 'uApplication.pas',
  regGui in 'regGui.pas',
  Vcl.Themes,
  Vcl.Styles,
  uPhpEvents in 'uPhpEvents.pas',
  core in 'core.pas',
  guiChromium in 'guiChromium.pas',
  guiForms in 'guiForms.pas';

{$R *.res}


begin
  Application.Initialize;

  CefOnBeforeCommandLineProcessing := procedure(const processType: ustring; const commandLine: ICefCommandLine)
  var
    len, i, R: integer;
    S: string;
  begin
    len := Length(cef_command_line) - 1;
    if len > 0 then
      for i := 0 to len do begin
        S := cef_command_line[i];
        R := Pos('=', S);
        if R = 0 then
          commandLine.AppendSwitch(S)
        else
          commandLine.AppendSwitchWithValue(Copy(S, 0, R - 1), Copy(S, R + 1));
      end
    else
      commandLine.AppendSwitch('no-proxy-server');
  end;
  CefSingleProcess := true;
  //if not CefLoadLibDefault then
    //exit;
  {
    @TODO:
    в многопоточном режиме при закрытии программы падает, чтоб не падало нужно выполнить
    загрузку либы через if not CefLoadLibDefault then exit;
    но в этом случае не получится оставить программу без libcef.dll если хром не нужен
    короче надо подумать потом как можно решить
  CefSingleProcess := False;
  if not CefLoadLibDefault then
    exit;
  }

  Application.MainFormOnTaskBar := false;
  Application.ShowMainForm      := false;

  Application.CreateForm(T__mainForm, __mainForm);
  Application.Run;

  CefShutDown();

end.
