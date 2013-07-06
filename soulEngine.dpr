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
  guiChromium in 'guiChromium.pas';

{$R *.res}


begin
  CefOnBeforeCommandLineProcessing := procedure(const processType: ustring; const commandLine: ICefCommandLine)
  begin
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

  Application.Initialize;

  Application.MainFormOnTaskBar := false;
  Application.ShowMainForm      := false;

  Application.CreateForm(T__mainForm, __mainForm);
  Application.Run;

  CefShutDown();

end.
