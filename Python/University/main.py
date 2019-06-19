# Importing the random library that will allow me to generate random numbers.
import random


# This is where the data for all the players, such as their names, scores and cards will be stored.
# This array is a dynamic array
all_players = []

# I have created an individual variable for the rounds as it could be used dynamically, if the players wish to
# have more rounds.
number_of_rounds = 4


# This function will be returning random card selection according to the cards we want to return.
def get_random_cards():
    # Creating a temporary array to return it after the data has been assigned.
    temporary_card_array = []

    # Using for loop in order to run the same instruction 4 times.
    for x in range(number_of_rounds):
        # This is where store for a card each value and name, and its suit with the value of the suit.
        random_number_for_face = random.randint(2, 14)
        random_number_for_suit = random.randint(15, 18)
        temporary_card_array.append([[random_number_for_face, value_to_face(random_number_for_face)],
                                     [random_number_for_suit, value_to_suit(random_number_for_suit)]])
    # This is where the function returns the data that has been created.
    return temporary_card_array


# This is where all the commands and the instructions will take place in order to make the application work.
def application():
    # Requesting the user to enter any name
    run_for_individual_players(int(input("How many are taking place to this game?")))
    print("\n\n\n")
    run_game_rounds()
    get_highest_card_from_all_players()
    display_player_scores()
    set_winner_based_on_score()
    print("\n\n Thank you for participating ont his game. It was a pleasure!")


# This function finds the winner of the game based on the score.
def set_winner_based_on_score():
    temp_array = [0]

    for player_id in range(len(all_players)-1):
        if all_players[player_id+1][2] > all_players[temp_array[0]][2]:
            temp_array.clear()
            temp_array.append(player_id + 1)

        elif all_players[player_id+1][2] == all_players[temp_array[0]][2]:
            temp_array.append(player_id + 1)

    display_winner_based_on_score(temp_array)


# This function has been designed to display multiple winners, in case if they are more than 2 players playing.
def display_winner_based_on_score(winner_ids):
    message_output = "The player"

    if len(winner_ids) != 1:
        message_output += "s"

    message_output += " that had the highest score"

    if len(winner_ids) != 1:
        message_output += " are "
    else:
        message_output += " is "

    for player_id in range(len(winner_ids)):
        message_output += all_players[player_id][0]
        if player_id < len(winner_ids) - 1:
            message_output += " and "

    message_output += " with a " + str(all_players[winner_ids[0]][2]) + " score"

    print(message_output)


# This function displays the score to each player. It has been designed,
# to display the data at the end of the rounds.
def display_player_scores():
    for player_id in range(len(all_players)):
        print("The Player", all_players[player_id][0], "scored", all_players[player_id][2],
              "based on his cards in total.")


# This function determines who are the winners, and displays them at the end.
# This function is capable of comparing data against 200 players.
def get_highest_card_from_all_players():
    temp_array = [0]

    for high_card in range(len(all_players)-1):
        if all_players[high_card+1][3][0][0] > all_players[temp_array[0]][3][0][0]:
            temp_array.clear()
            temp_array.append(high_card + 1)

        elif all_players[high_card+1][3][0][0] == all_players[temp_array[0]][3][0][0]:
            temp_array.append(high_card + 1)

    display_winner_based_on_cards(temp_array)


# Displays all the winners based on the cards they have. This function has been
# designed to compare data against 200 players.
def display_winner_based_on_cards(winner_ids):
    message_output = "The player"

    if len(winner_ids) != 1:
        message_output += "s"

    message_output += " that had the highest cards"

    if len(winner_ids) != 1:
        message_output += " are "
    else:
        message_output += " is "

    for player_id in range(len(winner_ids)):
        message_output += all_players[player_id][0]
        if player_id < len(winner_ids)-1:
            message_output += " and "

    message_output += " with a " + all_players[winner_ids[0]][3][0][1]

    print(message_output)


# This function runs all the rounds one by one.
def run_game_rounds():
    for game_round in range(number_of_rounds):
        pvp_per_round(game_round)


# This function returns the value of a number between 2-14 to a card face.
def value_to_face(number_received):
    # When the value is between 2 and 10, it will return the value as string.
    if 1 < number_received < 11:
        return str(number_received)

    # For each of the if statements, it will return a value according to the number that is between 11 and 14.
    elif number_received == 11:
        return "Jack"

    elif number_received == 12:
        return "Queen"

    elif number_received == 13:
        return "King"

    elif number_received == 14:
        return "Ace"

    # If the number does not fall into the numbers above, it will return unknown, to avoid errors.
    return "Unknown"


# This function returns the value of a number between 15-18 to a card suit.
def value_to_suit(number_received):

    if number_received == 15:
        return "Clubs"

    elif number_received == 16:
        return "Diamonds"

    elif number_received == 17:
        return "Hearts"

    elif number_received == 18:
        return "Spades"

    return "Unknown"


# This function displays individually each card.
def display_all_cards(player):
    print("The Random Cards that", all_players[player][0], " has received are as follows:")

    greater_card = 0

    for x in range(len(all_players[player][1])):
        print(all_players[player][1][x][0][1], "of", all_players[player][1][x][1][1])

        if all_players[player][1][x][0][0] > all_players[player][1][greater_card][0][0]:
            greater_card = x

    print("The best card is", all_players[player][1][greater_card][0][1], "of",
          all_players[player][1][greater_card][1][1])

    all_players[player].append([all_players[player][1][greater_card][0], all_players[player][1][greater_card][1]])


# This function sets up the base data that is needed before the beginning of the game.
# It gets each player's name, random cards, player scores and stores all of them in all_players array.
def run_for_individual_players(total_players):
    for for_player in range(total_players):
        player_name = input("Please enter a name for Player " + str(for_player + 1))
        all_players.append([player_name, get_random_cards()])
        set_player_score(for_player)
        display_all_cards(for_player)


# This is what mainly sets the score, using for loop in order to get all the numbers that used to generate
# the cards and sum them to make the total score.
def set_player_score(player_id):
    cur_score = 0
    for x in range(number_of_rounds):
        cur_score += all_players[player_id][1][x][0][0]

    all_players[player_id].append(cur_score)


# This function is the one that pull the data from all the players and finds which card has
# the highest value, and who is the winner.
def pvp_per_round(round_number):
    winner_id = 0
    message_to_display = "For round " + str(round_number+1) + " the cards that will be compared are:\n"
    for player_id in range(len(all_players)):

        if all_players[player_id][1][round_number][0][0] > all_players[winner_id][1][round_number][0][0]:
            winner_id = player_id

        message_to_display += all_players[player_id][1][round_number][0][1] + " of "
        message_to_display += all_players[player_id][1][round_number][1][1]

        if player_id < len(all_players)-1:
            message_to_display += " VS "

        else:
            message_to_display += "\n"

    message_to_display += "The winner of this round is " + all_players[winner_id][0] + "\n\n"

    print(message_to_display)


# This is the command that will run the function that will run the entire program.
application()
