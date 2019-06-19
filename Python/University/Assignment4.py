positions = ["1", "2", "3", "4", "5", "6", "7"]
lpd = ['F', 'F', 'F', " ", 'T', 'T', 'T']
lpd0 = ["T", "T", "T", " ", "F", "F", "F"]


def moveitem():
    print("When an item is selected, it can only be moved to either the right(Frogs) or left(Toads).")
    print(positions)
    print(lpd)
    x = int(input("Please input the position of the desired item you would like to move.\nMUST BE 1 to 7.\n"))
    z = x - 1  # this makes position 0 be number 1
    y = z + 1  # item in the next position for frogs
    w = y + 1  # item in the next 2 positions for frogs
    a = z - 1  # item in the next position for toads
    b = a - 1  # item in the next 2 positions for toads
    if lpd[z] == 'F' or lpd[z] == 'F' or lpd[z] == 'F':
        if lpd[y] == " ":
            lpd.insert(y, lpd.pop(z))
            print("Next position is empty, move accepted.")
            print(lpd)
        elif lpd[y] == "T" or lpd[y] == "T" or lpd[y] == "T" and lpd[w] == " ":
            lpd.insert(w, lpd.pop(z))
            print("Next position is occupied, but the one after is empty, therefore move accepted.")
            print(lpd)
        else:
            print("Invalid move.")
    elif lpd[z] == "T" or lpd[z] == "T" or lpd[z] == "T":
        if lpd[a] == " ":
            lpd.insert(a, lpd.pop(z))
            print("Next position is empty, move accepted.")
            print(lpd)
        elif lpd[a] == "F" or lpd[a] == "F" or lpd[a] == "F" and lpd[b] == " ":
            lpd.insert(b, lpd.pop(z))
            print("Next position is occupied, but the one after is empty, therefore move accepted.")
            print(lpd)
        else:
            print("Invalid move.")
            moveitem()
    choice = input("Do you want to reset/go to main menu, or continue?     c/r\n").lower()
    if choice == "c":
        pass
    else:
        menu()
    if lpd != lpd0:
        moveitem()
    elif lpd == lpd0:
        print("Congratulations, you've won!")
        print("The game has been resetted, do you want to play again?")
        menu()
    else:
        menu()


def watch():
    dem = ['F', 'F', 'F', " ", 'T', 'T', 'T']
    dem.insert(4, dem.pop(3))
    print(positions)
    print(dem)
    dem.insert(4, dem.pop(3))
    dem.insert(4, dem.pop(2))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(2, dem.pop(1))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(2, dem.pop(1))
    dem.insert(1, dem.pop(3))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(3, dem.pop(5))
    dem.insert(4, dem.pop(5))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(5, dem.pop(6))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(6, dem.pop(4))
    dem.insert(5, dem.pop(4))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(4, dem.pop(2))
    dem.insert(3, dem.pop(2))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(2, dem.pop(0))
    dem.insert(1, dem.pop(0))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(0, dem.pop(1))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(1, dem.pop(3))
    dem.insert(2, dem.pop(3))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(3, dem.pop(5))
    dem.insert(4, dem.pop(5))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(5, dem.pop(4))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(4, dem.pop(2))
    dem.insert(3, dem.pop(2))
    print("Next.")
    print(positions)
    print(dem)
    dem.insert(2, dem.pop(3))
    print("And, finally.")
    print(positions)
    print(dem)
    print("Final positions are:\n", dem)
    menu()


def menu():
    sel = int(input("Please input the number of the desired task.\n1. Play\n2. Watch the computer play\n3. Quit\n"))
    if sel == 1:
        print("Let's play then.")
        moveitem()
    elif sel == 2:
        watch()
    elif sel == 3:
        quit
    else:
        print("The value must be between 1 and 3")
        menu()


menu()