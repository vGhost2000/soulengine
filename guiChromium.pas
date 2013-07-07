unit guiChromium;

{$ifdef fpc}
{$mode delphi}{$H+}
{$endif}

interface

uses
  Classes, SysUtils, phpUtils, Forms, regGUI, Controls,
  zendTypes,
  ZENDAPI,
  phpTypes,
  PHPAPI,
  php4delphi,
  uPhpEvents,
  uPHPMod,
  Graphics, Dialogs, dwsHashtables,
  ceflib, cefvcl;

procedure InitializeGuiChromium(PHPEngine: TPHPEngine);

procedure chromium_allowedcall(ht: integer; return_value: pzval;
  return_value_ptr: pzval; this_ptr: pzval; return_value_used: integer;
  TSRMLS_DC: pointer); cdecl;

procedure chromium_settings(ht: integer; return_value: pzval;
  return_value_ptr: pzval; this_ptr: pzval; return_value_used: integer;
  TSRMLS_DC: pointer); cdecl;
procedure chromium_load(ht: integer; return_value: pzval; return_value_ptr: pzval;
  this_ptr: pzval; return_value_used: integer; TSRMLS_DC: pointer); cdecl;



type
  TExtension = class(TCefv8HandlerOwn)
  private

  protected
    function Execute(const Name: ustring; const obj: ICefv8Value;
      const arguments: TCefv8ValueArray; var retval: ICefv8Value;
      var Exception: ustring): boolean; override;
  end;

var
  cef_command_line: array of ustring;


implementation

var
  AllowedCall: TStringHashTable;

procedure chromium_settings;
var
  p: pzval_array;
  len: integer;
  S: string;
begin
  if ht <> 2 then
  begin
    zend_wrong_param_count(TSRMLS_DC);
    Exit;
  end;
  zend_get_parameters_my(ht, p, TSRMLS_DC);

  S := LowerCase(Z_STRVAL(p[0]^));
  if      S = 'cefcache'                  then cefcache                  := Z_STRVAL(p[1]^)
  else if S = 'cefuseragent'              then cefuseragent              := Z_STRVAL(p[1]^)
  else if S = 'cefproductversion'         then cefproductversion         := Z_STRVAL(p[1]^)
  else if S = 'ceflocale'                 then ceflocale                 := Z_STRVAL(p[1]^)
  else if S = 'ceflogfile'                then ceflogfile                := Z_STRVAL(p[1]^)
  else if S = 'cefjavascriptflags'        then cefjavascriptflags        := Z_STRVAL(p[1]^)
  else if S = 'cefresourcesdirpath'       then cefresourcesdirpath       := Z_STRVAL(p[1]^)
  else if S = 'ceflocalesdirpath'         then ceflocalesdirpath         := Z_STRVAL(p[1]^)
  else if S = 'cefbrowsersubprocesspath'  then cefbrowsersubprocesspath  := Z_STRVAL(p[1]^)

  else if S = 'ceflocalstoragequota'      then ceflocalstoragequota      := Z_LVAL(p[1]^)
  else if S = 'cefsessionstoragequota'    then cefsessionstoragequota    := Z_LVAL(p[1]^)

  else if S = 'cefremotedebuggingport'          then cefremotedebuggingport          := Z_LVAL(p[1]^)
  else if S = 'cefuncaughtexceptionstacksize'   then cefuncaughtexceptionstacksize   := Z_LVAL(p[1]^)
  else if S = 'cefcontextsafetyimplementation'  then cefcontextsafetyimplementation  := Z_LVAL(p[1]^)

  else if S = 'cefpackloadingdisabled'      then cefpackloadingdisabled      := Z_BVAL(p[1]^)
  else if S = 'cefsingleprocess'            then cefsingleprocess            := Z_BVAL(p[1]^)
  else if S = 'cefcommandlineargsdisabled'  then cefcommandlineargsdisabled  := Z_BVAL(p[1]^)
  else if S = 'cefreleasedcheck'            then cefreleasedcheck            := Z_BVAL(p[1]^)

  else if S = 'command_line_argument'   then begin
    len := Length(cef_command_line);
    SetLength(cef_command_line, len + 1);
    cef_command_line[len] := Z_STRVAL(p[1]^);
  end

  else zend_error(E_WARNING, Pointer('Unknown parameter: ' + LowerCase(Z_STRVAL(p[0]^))));


  dispose_pzval_array(p);
end;

procedure chromium_allowedcall;
var
  p: pzval_array;
  i: integer;
  s: ansistring;
  tmp: ^ppzval;
  arr: PHashTable;
begin
  if ht < 1 then
  begin
    zend_wrong_param_count(TSRMLS_DC);
    Exit;
  end;
  zend_get_parameters_my(ht, p, TSRMLS_DC);

  AllowedCall.Clear;

  if p[0]^^._type = IS_ARRAY then
    arr := p[0]^^.Value.ht
  else
    arr := nil;

  if arr <> nil then
  begin
    New(tmp);
    for i := zend_hash_num_elements(arr) - 1 downto 0 do
    begin
      zend_hash_index_find(arr, i, tmp);
      AllowedCall.SetValue(LowerCase(Z_STRVAL(tmp^^)), '');
    end;
    Dispose(tmp);
  end;

  dispose_pzval_array(p);
end;

procedure chromium_load;
begin
  CefLoadLibDefault;
end;

procedure V8_ZVAL(Value: ICefv8Value; arg: pzval);
var
  S: ansistring;
begin
  if Value.IsUndefined or Value.IsNull then
    ZVAL_NULL(arg)
  else if Value.IsBool then
    ZVAL_BOOL(arg, Value.GetBoolValue)
  else if Value.IsInt then
    ZVAL_LONG(arg, Value.GetIntValue)
  else if Value.IsDouble then
    ZVAL_DOUBLE(arg, Value.GetDoubleValue)
  else if Value.IsDate then
    ZVAL_DOUBLE(arg, Value.GetDateValue)
  else if Value.IsString then
  begin
    S := Value.GetStringValue;
    if S = '' then
      ZVAL_EMPTY_STRING(arg)
    else
      ZVAL_STRINGL(arg, PAnsiChar(S), Length(S), True);
  end
  else
    ZVAL_NULL(arg);
end;

function ZVAL_V8(arg: pzval): ICefv8Value;
begin
  case arg._type of
    IS_LONG: Result := TCefv8ValueRef.NewInt(arg.Value.lval);
    IS_DOUBLE: Result := TCefv8ValueRef.NewDouble(arg.Value.dval);
    IS_BOOL: Result := TCefv8ValueRef.NewBool(boolean(arg.Value.lval));
    IS_STRING: Result := TCefv8ValueRef.NewString(Z_STRVAL(arg));
    else
      Result := TCefv8ValueRef.NewNull;
  end;
end;

function TExtension.Execute(const Name: ustring; const obj: ICefv8Value;
  const arguments: TCefv8ValueArray; var retval: ICefv8Value;
  var Exception: ustring): boolean;
var
  S: ansistring;
  Args: pzval_array_ex;
  Return, Func: pzval;
  i: integer;
begin
   {if name = 'eval' then
   begin
      if length(arguments) > 0 then
      begin
         phpMOD.RunCode( arguments[0].GetStringValue );
      end;
   end else}
  if Name = 'call' then
  begin
    if length(arguments) > 0 then
    begin
      S := arguments[0].GetStringValue;
      if (AllowedCall.FSize = 0) or (not AllowedCall.HasKey(LowerCase(S))) then
      begin
        Result := False;
        exit;
      end;

      Func := MAKE_STD_ZVAL;
      Return := MAKE_STD_ZVAL;


      ZVAL_STRING(Func, PAnsiChar(S), True);

      SetLength(args, length(arguments) - 1);
      for i := 0 to high(args) do
      begin
        args[i] := MAKE_STD_ZVAL;
        V8_ZVAL(arguments[i + 1], args[i]);
      end;

      call_user_function(
        GetExecutorGlobals.function_table,
        nil,
        Func,
        Return,
        Length(Args),
        Args,
        phpMOD.psvPHP.TSRMLS_D
        );

      for i := 0 to high(args) do
      begin
        _zval_dtor(args[0], nil, 0);
      end;

      retval := ZVAL_V8(Return);
      Result := True;

      _zval_dtor(Func, nil, 0);
      _zval_dtor(Return, nil, 0);
    end;
  end;
end;

const
  code =
    'var PHP;' + 'if (!PHP)' + '  PHP = {};' + '(function() {' +
  (* '  PHP.eval = function(code) {'+
   '    native function eval(code);'+
   '    return eval(code);'+
   '  };'+   *)
    '  PHP.call = function(a1,a2,a3,a4,a5,a6,a7,a8,a9,a10,a11,a12,a13,a14,a15) {' +
    '    native function call(arguments);' + '    return call.apply(null, arguments);' +
    '  };' + '})();';

// after load chromium callback
procedure callback_CefLoadLib;
begin
  CefRegisterExtension('v8/PHP', code, TExtension.Create as ICefV8Handler);
end;

procedure InitializeGuiChromium(PHPEngine: TPHPEngine);
begin
  PHPEngine.AddFunction('chromium_settings', @chromium_settings);
  PHPEngine.AddFunction('chromium_load', @chromium_load);
  PHPEngine.AddFunction('chromium_allowedcall', @chromium_allowedcall);

{ vG TEMP COMMENT
  CefLoadLibAfter := callback_CefLoadLib; }
end;


initialization
  AllowedCall := TStringHashTable.Create(256);

end.
