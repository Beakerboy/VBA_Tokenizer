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
    While iVariable Is 2
        iVariable = iVariable + 1
    Wend
End Function

' Function: Bar
Private Sub Bar(Optional sTest As String)
    If Not sTest = "somevalue" And sTest > 2.6 Then
        iDoSomething = 5
    Elseif sTest = "something else" Or sTest = "Something Else" Then
        iDoSomethong = 6
    Else
        iDoSomething = 7
    End If
End Sub

Public Property Let (Baz)
    oSQL = Baz
    Do While 6 > 7
        Bar(2)
    Loop
End Property

Private Sub pSub ()
    For i = 1 To 6
        Lib.Save i
    Next i
    For Each element In vArray
        Lib2.Read
    Next
End Sub
