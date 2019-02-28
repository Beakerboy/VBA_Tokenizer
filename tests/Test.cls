VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Test"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Option Explicit

' Class: Test
' A test class.
Implements iTest

Private sTable As String
Public oSQL As oObject

' Function: Foo
Public Function Foo(iVariable As Double) As Boolean

End Function

' Function: Bar
Private Sub Bar(Optional sTest As String)
if sTest = "somevalue" Then
  iDoSomething = 5
Elseif sTest = "something else" Then
  iDoSomethong = 6
Else
  iDoSomething = 7
End If
End Sub
