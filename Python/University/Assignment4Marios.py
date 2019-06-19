currentPosition = ['F', 'F', 'F', " ", 'T', 'T', 'T']
generalLocations = ["1", "2", "3", "4", "5", "6", "7"]
previousPosition = ["T", "T", "T", " ", "F", "F", "F"]
pcVSpcLocations = ['F', 'F', 'F', " ", 'T', 'T', 'T']


def main():
    option = int(input("Please enter a number depending on the option you woud like to choose.\n1. Start Game\n2. Autoplay\n3. Exit\n"))

    if option == 1:
        print("The game has started.")
        createNewMove()
    elif option == 2:
        autoPlayer()
    elif option == 3:
        quit
    else:
        print("The option you have entered is non accurate. Please make sur you enter the correct option using a number.")
        main()


def createNewMove():
    print("If you wish to move the frogs, they can only move to the right by entering their position number. The same applies for Toads but they move to the left.")
    displayGamePosition()
    playerMove = int(input("Please enter your move to begin with a number between 1 to 7.\n"))

    if currentPosition[playerMove-1] == 'F' or currentPosition[playerMove-1] == 'F' or currentPosition[playerMove-1] == 'F':
        if currentPosition[playerMove] == " ":
            currentPosition.insert(x, currentPosition.pop(x-1))
            print("Movement accepted as a single jump")
            displayGamePosition()
        elif currentPosition[playerMove] == "T" or currentPosition[playerMove] == "T" or currentPosition[playerMove] == "T" and currentPosition[playerMove+1] == " ":
            currentPosition.insert(x+1, currentPosition.pop(x-1))
            print("Movement accepted as a double jump")
            displayGamePosition()
        else:
            print("Invalid move.")

    elif currentPosition[playerMove-1] == "T" or currentPosition[playerMove-1] == "T" or currentPosition[playerMove-1] == "T":
        if currentPosition[playerMove-2] == " ":
            currentPosition.insert(a, currentPosition.pop(x-1))
            print("Movement accepted as a single jump.")
            displayGamePosition()
        elif currentPosition[playerMove-2] == "F" or currentPosition[playerMove-2] == "F" or currentPosition[playerMove-2] == "F" and currentPosition[playerMove-3] == " ":
            currentPosition.insert(b, currentPosition.pop(x-1))
            print("Movement accepted as a double jump")
            displayGamePosition()
        else:
            print("Invalid move.")
            createNewMove()
    choice = input("To continue enter 1, otherwise enter any key to go back the main menu. \n").lower()

    if choice == "1":
        pass

    else:
        main()
    if currentPosition != previousPosition:
        createNewMove()

    elif currentPosition == previousPosition:
        print("You have managed to win the game. Congratulations!")
        main()
    else:
        main()


def autoPlayer():
    global pcVSpcLocations
    pcVSpcLocations = ['F', 'F', 'F', " ", 'T', 'T', 'T']

    autogameMoves = [[4, 3], [4, 2], [2, 1], [2, 1], [1, 3], [3, 5], [4, 5], [5, 6], [6, 4], [5, 4], [4, 2], [3, 2],
                     [2, 0], [1, 0], [0, 1], [1, 3], [2, 3], [3, 5], [4, 5], [5, 4], [4, 2], [3, 2], [2, 3], [0, 0]]

    numMoves = [1, 2, 1, 2, 2, 1, 2, 2, 2, 2, 1, 2, 2, 1, 2]
    moveCounter = 0

    print("The game has started.")
    displayPosition()
    pcVSpcLocations.insert(4, pcVSpcLocations.pop(3))

    for round in range (len(numMoves)):
        print("Next.")
        displayPosition()

        if round!=14:
            pcVSpcLocations.insert(autogameMoves[moveCounter][0], pcVSpcLocations.pop(autogameMoves[moveCounter][1]))

            moveCounter += 1

            if numMoves[round] == 2:
                pcVSpcLocations.insert(autogameMoves[moveCounter][0], pcVSpcLocations.pop(autogameMoves[moveCounter][1]))
                moveCounter += 1

    print("And, finally.")
    displayPosition()

    print("Final Locations are:\n", pcVSpcLocations)
    main()


def displayPosition():
    print(generalLocations)
    print(pcVSpcLocations)

def displayGamePosition():
    print(generalLocations)
    print(currentPosition)


main()