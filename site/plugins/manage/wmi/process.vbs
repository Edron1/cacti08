
Set objArgs = WScript.Arguments

o=lcase(objArgs(1))

strComputer = objArgs(0)
Set objWMIService = GetObject("winmgmts:{impersonationLevel=impersonate}!\\" & strComputer & "\root\cimv2")
Set colListOfServices = objWMIService.ExecQuery("Select * from win32_process")

Set pid = CreateObject("Scripting.Dictionary")
j=0
For Each objService in colListOfServices
  pid.item(j)=objService.name
  j=j+1
Next

ok=0
For i = 0 to j-1
  if o = lcase(pid.item(i)) then
    ok=1
  end if
next

if ok = 1 then
  WScript.Echo "up"
else
  WScript.Echo "down"
end if

