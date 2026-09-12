' =====================================================================
'   KHO HANG CUA THANH GIAU - CHAY MAY CHU NGAM CHO MAY POS (KHONG CUA SO)
' =====================================================================
Set WshShell = CreateObject("WScript.Shell")
Set FSO = CreateObject("Scripting.FileSystemObject")
ScriptDir = FSO.GetParentFolderName(WScript.ScriptFullName)

' Khoi dong may chu an hoan toan duoi nen (windowStyle = 0)
WshShell.Run "cmd.exe /c """ & ScriptDir & "\CHAY_MAY_CHU_ONLINE.bat""", 0, False
