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

  {$IFDEF VS_EDITOR}
  uPHPCatButtons in 'uPHPCatButtons.pas',
  uVSEditor in 'uVSEditor.pas',
  {$ENDIF}

  regGui in 'regGui.pas',
  Vcl.Themes,
  Vcl.Styles;

{$R *.res}


begin
  Application.Initialize;

  Application.MainFormOnTaskBar := false;
  Application.ShowMainForm      := false;

  Application.CreateForm(T__mainForm, __mainForm);

  Application.Run;

end.
