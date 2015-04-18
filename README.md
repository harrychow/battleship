# battleship
Battleship Exercise

Create a simple web application to allow a single human player to play a one-
sided game of battleships against the computer.

The program should create a 10x10 grid, and place a number of ships on the grid at 
random with the following sizes:

 1 x Battleship (5 squares)
 2 x Destroyers (4 squares)

Ships can touch but they must not overlap.
The application should accept input from the user in the format “A5” to signify a square 
to target, and feedback to the user whether the shot was success, miss, and additionally 
report on the sinking of any vessels.
. = no shot
- = miss
X = hit

Example output

Miss
 1234567890
A -.........
B ..........
C ..........
D ..........
E ..........
F ..........
G ..........
H ..........
I ..........
J ..........
Enter coordinates (row, col), e.g. A5 =

You should implement a show command to aid debugging and backdoor cheats. 
Example output after entering show

 1234567890
A X 
B X 
C X X 
D X X 
E X X 
F X 
G 
H 
I XXXX
J 
Enter coordinates (row, col), e.g. A5 =

Please report the number of shots taken once game complete, e.g.

Sunk
 1234567890
A ......X...
B ......X...
C ..X...X...
D ..X...X...
E ..X...X...
F ..X.......
G ..........
H ..........
I .....-XXXX
J ..........
Well done! You completed the game in 14 shots
