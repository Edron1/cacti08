
Set objArgs = WScript.Arguments

o=lcase(objArgs(1))
For i = 2 to objArgs.Count - 1	  
  o=o+" "+objArgs(i)	
Next

strComputer = objArgs(0)
Set objWMIService = GetObject("winmgmts:{impersonationLevel=impersonate}!\\" & strComputer & "\root\cimv2")
Set colListOfServices = objWMIService.ExecQuery("Select * from Win32_Service Where State='Running'")

Set pid = CreateObject("Scripting.Dictionary")
j=0
For Each objService in colListOfServices
  n = ""
  For i = 1 to len(objService.DisplayName)
    ok=0
    replace_space=0
    extrait = mid(objService.DisplayName,i,1)
    If extrait <> "'" Then
      If extrait <> "�" Then
        If extrait <> "�" Then
          If extrait <> "�" Then
            ok=1
          else
            replace_space=1
          end if
        else
          replace_space=1
        End If
      else
        replace_space=1
      End If
    End If

    If ok = 1 Then
      n=n+extrait
    End If

    If replace_space = 1 Then
      if i <> len(objService.DisplayName) then
        n=n+"  "
      end if
    End If

    precedent = extrait
  next

  pid.item(j)=n
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


