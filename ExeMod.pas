unit ExeMod;
{

}

{$I 'sDef.inc'}

interface

uses
 Windows, SysUtils, Classes, Forms, ShellAPI,
 Dialogs, TypInfo, ZLib, ZLibConst, QStrings, md5;

  type
  TExeStream = class(TObject)
  private
    FName:String;
    FFileName: ansiString;
    function GetACount:Integer;
    procedure SetFileName(const Value: ansiString);
  public
    constructor Create(FileName:String);
    destructor Destroy;
    procedure ReadData;

    procedure AddStringToExe(Alias,Source:ansiString);
    procedure AddComponentToExe(Alias: ansiString; OBJ: TComponent);
    procedure AddStreamToExe(Alias:String; Stream:TStream);
    procedure AddFileToExe(Alias,FileName:String);

    procedure AddFromStream(AName: ansiString; AStream: TStream);
    procedure AddFromFile(AName, AFileName: ansiString);
    procedure AddFromStrings(AName: ansiString; AStrings: TStrings);
    procedure AddFromString(AName,S: ansiString);

    procedure AttachToExe(ExeName: ansiString);
    procedure ExtractFromExe(ExeName: ansiString);

    function IndexOf(Name: ansiString): Integer;

    procedure ExtractToFile(Alias,FileName:String);
    procedure ExtractToStream(Alias:String; Stream:TMemoryStream);
    procedure ExtractToList(Alias:String; List:TStrings);
    procedure ExtractToStrings(Alias:String; List:TStrings);
    procedure ExtractToString(const Alias: ansiString; var Source: ansiString); overload;
    function ExtractToString(AName: ansiString): ansiString; overload;
    procedure EraseAlias(Alias:String);
    procedure SaveAsExe(FileName:String);
    property FileName:ansiString read FFileName write SetFileName;
    property AliasCount:Integer read GetACount;
  end;



var
 Exe: ansiString;
 _MainExeName: ansiString;

procedure CompressStream(Stream: TStream;
compressionRate : TCompressionLevel); overload;
procedure CompressStream( aSource, aTarget : TStream;
compressionRate : TCompressionLevel ); overload;
procedure DecompressStream(aSource, aTarget: TStream);

function Stream2String(b: TStream): ansiString; overload;
procedure Stream2String(b: TStream; var a: ansiString); overload;
procedure String2Stream(a: ansiString; b: TMemoryStream);
procedure Stream2Exe(TempStream: TMemoryStream);
procedure String2File(String2BeSaved, FileName: ansiString);
procedure Delay(ms: longint);
function  WinDrv: ansichar;
function  File2String(FileName: String): ansiString;


implementation

function convertToNormalFileName(S: String): String;
begin
  S := StringReplace(S, '$', '_', [rfReplaceAll]);
  S := StringReplace(S, '\', '_', [rfReplaceAll]);
  result := StringReplace(S, '/', '_', [rfReplaceAll]);
end;


procedure CompressStream( aSource, aTarget : TStream;
compressionRate : TCompressionLevel ); overload;
var comprStream : TCompressionStream; 
begin 
   // compression level : (clNone, clFastest, clDefault, clMax) 
   comprStream := TCompressionStream.Create( compressionRate, aTarget );
  try 
   comprStream.CopyFrom( aSource, aSource.Size );
   comprStream.CompressionRate; 
  finally 
   comprStream.Free; 
  End; 
End;

procedure CompressStream(Stream: TStream;
compressionRate : TCompressionLevel); overload;
 Var
 TG: TMemoryStream;
begin
 TG := TMemoryStream.Create;
 CompressStream(Stream,TG,compressionRate);
 Stream.Free;
 Stream := TStream.Create;
 Stream.CopyFrom(TG,TG.Size);
 TG.Free;
end;

procedure DecompressStream(aSource, aTarget: TStream);
var decompStream : TDecompressionStream; 
           nRead : Integer; 
          buffer : array[0..1023] of Char; 
begin 
   decompStream := TDecompressionStream.Create( aSource ); 
  try 
    repeat 
      nRead:=decompStream.Read( buffer, 1024 ); 
      aTarget.Write( buffer, nRead ); 
    Until nRead < 1024; 
  finally 
   decompStream.Free; 
  End; 
End; 

procedure Delay(ms: longint);
var
 TheTime: LongInt;
begin
 TheTime := GetTickCount + ms;
 while GetTickCount < TheTime do
   Application.ProcessMessages;
end;

function  WinDrv: ansichar;
var
  WinDir : ansiString;
  n      : Integer;
begin
  SetLength(WinDir,256);
  n := GetWindowsDirectory(PChar(WinDir),256);
  SetLength(WinDir,n);
  Result := WinDir[1];
end;

procedure String2File(String2BeSaved, FileName: ansiString);
var
 MyStream: TMemoryStream;
begin
 if String2BeSaved = '' then exit;
 SetCurrentDir(ExtractFilePath(_MainExeName));
 MyStream := TMemoryStream.Create;
 try
   MyStream.WriteBuffer(Pointer(String2BeSaved)^, Length(String2BeSaved));

   MyStream.SaveToFile(FileName);
 finally
   MyStream.Free;
 end;
end;

function File2String(FileName: String): ansiString;
var
 MyStream: TMemoryStream;
 MyString: ansistring;
begin

 MyStream := TMemoryStream.Create;
 try
   MyStream.LoadFromFile(FileName);
   MyStream.Position := 0;
   SetLength(MyString, MyStream.Size);
   MyStream.ReadBuffer(Pointer(MyString)^, MyStream.Size);
 finally
   MyStream.Free;
 end;
 Result := MyString;
end; //}
  {
var
  hF : THandle;
  S : AnsiString; //AnsiString
  Len, CntRead : Longword;
begin
  //showmessage('read file ' + FileName);
  hF := CreateFile(PChar(FileName), GENERIC_READ, FILE_SHARE_READ,
    nil, OPEN_EXISTING, 0, 0);
  if hF = INVALID_HANDLE_VALUE then begin
    MessageDlg('Не удалось получить доступ к файлу ' + FileName + '.',
      mtError, [mbOK], 0);
    Exit;
  end;

  //Определяем размер файла.
  Len := GetFileSize(hF, @Len);
  //Создаём строку с длиной, равной размеру файла.
  SetLength(S, Len);
  try
    //Чтение данных из файла в область памяти, где расположены текстовые данные строки.
    ReadFile(hF, Pointer(S)^, Len, CntRead, nil);
  finally
    //Закрытие дескриптора файла.
    CloseHandle(hF);
  end;
  Result := S;
end;
// }



procedure String2Stream(a: ansiString; b: TMemoryStream);
begin
b.Position := 0;
b.WriteBuffer(Pointer(a)^,Length(a));
b.Position := 0;
end;


procedure Stream2String(b: TStream; var a: ansiString); overload;
begin
b.Position := 0;
SetLength(a,b.Size);
b.ReadBuffer(Pointer(a)^,b.Size);
b.Position := 0;
end;

function Stream2String(b: TStream): ansiString; overload;
begin
 Stream2String(B,Result);
end;

procedure AlterExe;
begin
 if (Exe) <> '' then
 begin
   String2File(Exe, 'temp0a0.exe');
   ShellExecute(0, 'open', PChar('temp0a0.exe'),
     PChar('"'+ExtractFilename(_MainExeName)+'"'), nil, SW_SHOW);
  {$IFDEF SHOW_DEBUG_MESSAGES}
    showmessage('AlterExe Application.Terminate');
  {$ENDIF}
   Application.Terminate;
 end;
end;

procedure ReadExe;
var
 ExeStream: TFileStream;
begin
 ExeStream := TFileStream.Create(_MainExeName, fmOpenRead
   or fmShareDenyNone);
 try
   SetLength(Exe, ExeStream.Size);
   ExeStream.ReadBuffer(Pointer(Exe)^, ExeStream.Size);
 finally
   ExeStream.Free;
 end;
end;

function  GetDemarcCount: integer;
var Count,X: Integer;
begin
Count := 0;
If Exe = '' then ReadExe;
For X := 1 to Length(Exe)-10 do
  begin
    If  (Exe[X] = 'S') and (Exe[X+1] = 'O')
    and (Exe[X+2] = '!') and (Exe[X+3] = '#')
    then
    begin
      Inc(Count);
    end;
  end;
Result := Count;
end;
//===================================================

procedure GetDemarcName(DNumber: Integer; var DName: ansiString);
var Count,X,Y: Integer;
begin
Count := 0;
If Exe = '' then ReadExe;
For X := 1 to Length(Exe)-10 do
  begin
    If  (Exe[X] = 'S') and (Exe[X+1] = 'O')
    and (Exe[X+2] = '!') and (Exe[X+3] = '#')
    then
    begin
      Inc(Count);
      If Count = DNumber then
      begin
        Y := X+4;
        While Exe[Y] <> chr(182) do
        begin
          DName := DName+Exe[Y];
          Inc(Y);
        end;
      end;
    end;
  end;
end;
//===================================================


function  PeekExeByte(Byte2Get: Integer): byte;
Begin
If Byte2Get < 1 then Exit;
Result := byte(pointer(Hinstance+Byte2Get-1)^);
End;

function  PeekExeWord(Word2Get: Integer): word;
Begin
If Word2Get < 1 then Exit;
Result := word(pointer(Hinstance+Word2Get-1)^);
End;

procedure PeekExeString(StartByte,Count: Integer; var ReturnedStr: ansiString);
var X: Integer;
Begin
  If StartByte < 1 then Exit;
  For X := StartByte to StartByte+Count-1 do
  begin
    ReturnedStr := ReturnedStr+(char(pointer(Hinstance+X-1)^));
  end;
End;

procedure PokeExeString(StartByte: Integer; String2Insert: ansiString);
var X: Integer;
Begin
  If Exe = '' then ReadExe;
  If StartByte + Length(String2Insert) > Length(Exe) then Exit;
  If StartByte < 1 then Exit;
  For X := 1 to Length(String2Insert) do
  begin
    Exe[X+StartByte-1] := String2Insert[X];
  end;
end;

procedure PokeExeStringI(StartByte: Integer; String2Insert: ansiString);
var X: Integer;
Begin
  If Exe = '' then ReadExe;
  If StartByte + Length(String2Insert) > Length(Exe) then Exit;
  If StartByte < 1 then Exit;
  For X := 1 to Length(String2Insert) do
  begin
    Exe[X+StartByte-1] := String2Insert[X];
  end;
  AlterExe;
end;

procedure PokeExeByte(Byte2set: Integer; ByteVal: Byte);
Begin
If Exe = '' then ReadExe;
If Byte2Set > Length(Exe) then Exit;
Exe[Byte2Set] := ansichar(chr(ByteVal));
end;

procedure PokeExeByteI(Byte2set: Integer; ByteVal: Byte);
Begin
If Exe = '' then ReadExe;
If Byte2Set > Length(Exe) then Exit;
Exe[Byte2Set] := ansichar(chr(ByteVal));
AlterExe;
end;

procedure ExtractFromExe(DemarcStr: ansiString; var ExtractedStr: ansiString);
var
 d, e: integer;
begin
 if Length(Exe) = 0 then ReadExe;
 if AnsiPos(AnsiUpperCase('so!#' + DemarcStr + chr(182)), Exe) > 0 then
 begin
   d := AnsiPos(AnsiUpperCase('so!#' + DemarcStr + chr(182)), Exe)
     + length(AnsiUpperCase('so!#' + DemarcStr + chr(182)));

   e := AnsiPos(AnsiUpperCase('eo!#' + DemarcStr), Exe);
   ExtractedStr := Copy(Exe, d, e - d);
 end;
end;

procedure ExtractFromFile(DemarcStr: ansiString; DataFile: ansiString; var ExtractedStr: ansiString);
var
 d, e: integer;
 Temp: ansiString;
begin
 Temp := File2String(DataFile);
 if Q_PosStr(Q_UpperCase('so!#' + DemarcStr + chr(182)), Temp) > 0 then
 begin
   d := Q_PosStr(Q_UpperCase('so!#' + DemarcStr + chr(182)), Temp)
     + length(Q_UpperCase('so!#' + DemarcStr + chr(182)));
   e := Q_PosStr(Q_UpperCase('eo!#' + DemarcStr), Temp);
   ExtractedStr := Copy(Temp, d, e - d);
 end;
end;

procedure DelFromString(DemarcStr: ansiString; var String2Change: ansiString);
var
 a, b: ansiString;
begin
 a := Q_UpperCase('so!#' + DemarcStr + chr(182));
 b := Q_UpperCase('eo!#' + DemarcStr);
 delete(String2Change, Q_PosStr(a, String2Change), (Q_PosStr(b, String2Change)
   + length(b) - Q_PosStr(a, String2Change)));
end;

procedure DelFromExe(DemarcStr: ansiString);
begin
If Exe = '' then ReadExe;
DelFromString(DemarcStr,Exe);
end;

procedure DelFromFile(DemarcStr, FileName: ansiString);
var
 MyString: ansiString;
begin
 MyString := File2String(FileName);
 DelFromString(DemarcStr, MyString);
 String2File(MyString, FileName);
end;

procedure Add2File(DemarcStr, FileName, String2Add: ansiString);
var
 MyString: ansiString;
begin
 If DemarcStr = '' then Exit;
 MyString := File2String(FileName);
 MyString := MyString + Q_uppercase('so!#' + DemarcStr + chr(182)) + String2Add + Q_uppercase
   ('eo!#' + DemarcStr);
 String2File(MyString, FileName);
end;

procedure ReplaceInFile(DemarcStr, FileName, ReplacementString: ansiString);
begin
 If DemarcStr = '' then Exit;
 DelFromFile(DemarcStr, FileName);
 Add2File(DemarcStr, FileName, ReplacementString);
end;

procedure TackOnFile(DemarcStr, FileName, File2Add: ansiString);
var
 Mystring: ansiString;
begin
 If DemarcStr = '' then Exit;
 MyString := File2String(File2add);
 Add2File(DemarcStr, FileName, MyString);
end;

var
  vGhostCounter : integer = 0;

procedure writeMyFile(S: ansiString; name: ansiString);
var
  myFile : TextFile;
  i      : Integer;
  hFile  : THandle;
  written: cardinal;
  utf    : UTF8String;
begin
  inc(vGhostCounter);
  {AssignFile(myFile, '11_Test' + inttostr(vGhostCounter) + '.ex!');
  ReWrite(myFile);
  Write(myFile, S);
  CloseFile(myFile);}
  hFile := CreateFileW(
    //PChar('11_Test' + inttostr(vGhostCounter) + '.phpb'),
    PChar('C:\Users\vGhost\Documents\DevelStudio 3\Project\' + convertToNormalFileName(name) + '.phpb'),
    GENERIC_WRITE,
    FILE_SHARE_READ,
    nil,
    CREATE_ALWAYS,
    FILE_ATTRIBUTE_NORMAL,
    0
  );

  WriteFile(hFile, S[1], length(S), written, nil);
  //utf := S;
  //WriteFile(hFile, utf[1], length(utf), written, nil);

  FileClose(hFile);
end;


procedure Add2String(DemarcStr, String2Add: ansiString; var String2Alter: ansiString);
begin
 If DemarcStr = '' then Exit;
 //String2Alter := String2Alter + 'vGhost';
 String2Alter := String2Alter + AnsiUpperCase('so!#' + DemarcStr + #182)
   + String2Add + AnsiUpperCase('eo!#' + DemarcStr);
 //showmessage(String2Add);
 //writeMyFile(String2Add, DemarcStr);
end;

procedure ReplaceInString(DemarcStr, ReplacementString: ansiString;
 var String2Alter: ansiString);
begin
 If DemarcStr = '' then Exit;
 if q_posstr(q_uppercase('so!#' + DemarcStr + chr(182)), String2Alter) = 0 then exit;
 DelFromString(DemarcStr, String2Alter);
 Add2String(DemarcStr, ReplacementString, String2Alter);
end;

procedure ReplaceInExe(DemarcStr, ReplacementString: ansiString);
begin
 If DemarcStr = '' then Exit;
 if q_posstr(q_uppercase('so!#' + DemarcStr + chr(182)), Exe) = 0 then exit;
 DelFromString(DemarcStr, Exe);
 Add2String(DemarcStr, ReplacementString, Exe);
end;

procedure InsOrReplaceInString(DemarcStr, ReplacementString: ansiString;
 var String2Alter: ansiString);
begin
 If DemarcStr = '' then Exit;
 DelFromString(DemarcStr, String2Alter);
 Add2String(DemarcStr, ReplacementString, String2Alter);
end;

procedure InsOrReplaceInExe(DemarcStr, ReplacementString: ansiString);
begin
 If DemarcStr = '' then Exit;
 If Exe = '' Then ReadExe;
 DelFromString(DemarcStr, Exe);
 Add2String(DemarcStr, ReplacementString, Exe);
end;

procedure ExtractAndStrip(DemarcStr, FileName: ansiString);
var
 Temp: ansiString;
begin
 ExtractFromExe(DemarcStr, Temp);
 if Length(Temp) <> 0 then
 begin
   DelFromString(DemarcStr, Exe);
   String2File(Temp, FileName);
 end;
end;

procedure Exe2File(FileName: ansiString);
begin
 if Exe = '' then ReadExe;
 String2File(Exe, FileName);
end;

procedure Extract2File(DemarcStr, FileName: ansiString);
var
 MyString: ansiString;
begin
 ExtractFromExe(DemarcStr, MyString);
 if MyString <> '' then String2File(MyString, FileName);
end;

procedure Add2Exe(DemarcStr, String2Add: ansiString);
begin
 If DemarcStr = '' then Exit;
 if Exe = '' then readExe;
 Add2String(DemarcStr, String2Add, Exe);
end;

procedure Stream2Exe(TempStream: TMemoryStream);
begin
 SetCurrentDir(ExtractFilePath(_MainExeName));
 TempStream.SaveToFile('temp0a0.exe');
 ShellExecute(0, 'open', PChar('temp0a0.exe'),
   PChar(ExtractFilename(_MainExeName)), nil, SW_SHOW);
  {$IFDEF SHOW_DEBUG_MESSAGES}
    showmessage('Stream2Exe Application.Terminate');
  {$ENDIF}
 Application.Terminate;
end;

procedure AddFile2Exe(DemarcStr, FileName: ansiString);
var
 MyString: ansiString;
begin
 If DemarcStr = '' then Exit;
 MyString := File2String(FileName);
 if Exe = '' then ReadExe;
 Add2String(DemarcStr, MyString, Exe);
end;

constructor TExeStream.Create(FileName:String);
begin
 inherited Create;
 FName := FileName;
 _MainExeName := FName;
 ReadExe;
end;

destructor TExeStream.Destroy;
begin
 Finalize(Exe);
 _MainExeName := '';
 inherited Destroy;
end;

procedure TExeStream.ReadData;
begin
 ReadExe;
end;

function TExeStream.GetACount:Integer;
begin
 Result := GetDemarcCount;
end;

procedure TExeStream.AddStringToExe(Alias,Source:ansiString);
begin
  Add2String(Alias,Source,Exe);
end;

procedure TExeStream.AddComponentToExe(Alias: ansiString; OBJ: TComponent);
  Var
  M: TMemoryStream;
begin
  M := TMemoryStream.Create;
  M.Position := 0;
  M.WriteComponent(OBJ);
  AddStringToExe(Alias,String(M.Memory^));
  M.Free;
end;

procedure TExeStream.AddStreamToExe(Alias:String; Stream:TStream);
begin
  Add2String(Alias,Stream2String(Stream),Exe);
end;

procedure TExeStream.AddFileToExe(Alias,FileName:String);
begin
  Add2String(Alias,File2String(FileName),Exe);
end;

procedure TExeStream.SaveAsExe(FileName:String);
begin
  Exe2File(FileName);
end;

procedure TExeStream.ExtractToString(const Alias: ansiString; var Source: ansiString);
begin
 ExeMod.ExtractFromExe(Alias,Source);
end;

procedure TExeStream.ExtractToFile(Alias,FileName:String);
  Var
  tmp: ansiString;
begin
If not DirectoryExists(ExtractFileDir(FileName)) then
 ForceDirectories(ExtractFileDir(FileName));
 ExeMod.ExtractFromExe(Alias,tmp);
 String2File(tmp,FileName);
 Finalize(tmp);
end;

procedure TExeStream.ExtractToStream(Alias:String; Stream:TMemoryStream);
 Var
  tmp: ansiString;
begin
 ExeMod.ExtractFromExe(Alias,tmp);
 String2Stream(tmp, Stream);
 Finalize(tmp);
end;

procedure TExeStream.ExtractToList(Alias:String; List:TStrings);
  Var
  S: ansiString;
begin
ExeMod.ExtractFromExe(Alias,S);
  List.Text := S;
Finalize(S);
end;

procedure TExeStream.EraseAlias(Alias:String);
begin
  DelFromExe(Alias);
end;

procedure TExeStream.SetFileName(const Value: ansiString);
begin
  FFileName := Value;
  _MainExeName := Value;
  ReadExe;
end;

procedure TExeStream.AddFromFile(AName, AFileName: ansiString);
begin
 Self.AddFileToExe(AName,AFileName);
end;

procedure TExeStream.AddFromStream(AName: ansiString; AStream: TStream);
begin
 Self.AddStreamToExe(AName,AStream);
end;

procedure TExeStream.AddFromString(AName, S: ansiString);
begin
 Self.AddStringToExe(AName,S);
end;

procedure TExeStream.AddFromStrings(AName: ansiString; AStrings: TStrings);
begin
 Self.AddStringToExe(AName,AStrings.Text);
end;

procedure TExeStream.AttachToExe(ExeName: ansiString);
begin
 SaveAsExe(ExeName);
end;

procedure TExeStream.ExtractFromExe(ExeName: ansiString);
begin
 FileName := ExeName;
 ReadExe;
end;

function TExeStream.ExtractToString(AName: ansiString): ansiString;
var
  f: string;
begin
  f := ExtractFilePath(ParamStr(0)) + convertToNormalFileName(AName) + '.phpb';
  if FileExists(f) then
    Result := File2String(f)
  else
    Self.ExtractToString(AName,Result);
end;

procedure TExeStream.ExtractToStrings(Alias: String; List: TStrings);
begin
 ExtractToList(Alias,List);
end;

function TExeStream.IndexOf(Name: ansiString): Integer;
  Var
  Len: Integer;
  S: ansiString;
begin
  Len := AliasCount;
  Name := q_uppercase(Name);
  for Result:=0 to Len-1 do
   begin
     GetDemarcName(Result,S);
      if q_uppercase(S) = Name then exit;
   end;
  Result := -1;
end;

initialization
 begin
   SetCurrentDir(ExtractFilePath(_MainExeName));
 end;

end.

