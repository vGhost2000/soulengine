program soulEngine;

{$I 'sDef.inc'}

uses
  Forms,
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
  core in 'core.pas';

{$R *.res}


begin
  Application.Initialize;

  Application.MainFormOnTaskBar := false;
  Application.ShowMainForm      := false;

  Application.CreateForm(T__mainForm, __mainForm);
  Application.Run;

end.
