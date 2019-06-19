# This is the list that will be used accross the software and assigned by option 1
main_word_list = list()

# This function is what will run the software.
def main():
    # nothing
    action = 0

    # if the user enters number 5, it will kill the software.
    while action != 5:

        # Prints the options available for the user.
        print_actions()

        # requestion user to enter a numbr
        action = int(input("Please enter your option from 1 to 5: "))

        # Runs the function that will execute the required function according to the selection
        execute_according_action(action)

    # Printing a message as a good bye to the user.
    print("Thank you for using this software.")



# This function runs in order to execute the neccesary function according to the user's input.
def execute_according_action(action):
    # Graps the global variable that was assigned at the beginning of the software.
    global main_word_list

    # If the selected action was one, it will invite the user to enter a word.
    if action == 1:
        enter_word_to_main_list()


    # if the action number is between 2 and 5, it will execute this if statement.
    elif 1 < action < 6 and len(main_word_list) > 0:
        if action == 2:
            check_times_involving_letter()

        elif action == 3:
            isPalidrome()

        elif action == 4:
            isAnagram()


    # If is something else, it will display this message.
    else:
        print("Sorry but you firstly have to select option 1 and enter a word in order to continue...")


# Based on the string parameter that was passed, it changes it to a list.
def string_to_list(word):
    temp_list = []
    for letter in word:
        temp_list.append(letter)

    return temp_list


# Based on the list parameter that was passed, it changes it to a string.
def list_to_string(list_requested):
    temp_string = ""

    for index in range(len(list_requested)):
        if type(list_requested[index]) == str:
            temp_string += list_requested[index]

        else:
            temp_string = ""
            index = len(list_requested) + 1

    return temp_string


# This reverses the values of the keys. The last keys become first, and the first go at the end.
def reverse_list(list_to_reverse):
    temp_list = []

    for index in range(len(list_to_reverse)):
        temp_list.append(list_to_reverse[len(list_to_reverse) -index -1])

    return temp_list


# This checks how many times a character has been involved on the word entered on option 1
def check_times_involving_letter():
    global main_word_list

    # Invites user to enter a character.
    character = input("Please enter a character that you want to check how many times it exists on the word entered: ")

    # sets that character to lower case letters.
    character = character.lower()

    # This has been used to count the times that the character has been found on the string.
    countTimes = 0

    # Run the for loop to get the index based on the length of the list.
    for index in range(len(main_word_list)):

        # if the character entered with the value of the index are equal, the countTimes will increase.
        if character == main_word_list[index]:
            countTimes+=1

    # Prints the results.
    print("The character", character, "exists", str(countTimes), "times in the word", list_to_string(main_word_list))


# This is similar to sort, based on the parameter passed.
def place_list_in_order(requested_list):
    # Creates a temporary list, needed for the function.
    temp_array = []

    # Condition for while loop: While there are still values inside the parameter, keep repeating yourself.
    while requested_list:
        # Assigning the first character for the list to a variable.
        first_character = requested_list[0]

        # Creating this for loop so it can get each letter one by one and run it through a check.
        for character in requested_list:
            # If the character is smaller than the first character of the requested list, then it will set this character as a first letter.
            if character < first_character:
                first_character = character

        # This will add this first character into the temp_array
        temp_array.append(first_character)

        # This will remove the value from the requested array
        requested_list.remove(first_character)

    # Once this function is done, it will return the results.
    return temp_array


# Just printing the available options to the user.
def print_actions():
    print("\n")
    print("What would you like to do? Enter a number according to the option you want to execute.")
    print("1. Enter a word that will be used accross the software.")
    print("2. Check how many times a character appears to the word.")
    print("3. Check if the word is palidrome.")
    print("4. Check if the word is an anagram word.")
    print("5. Exit the software.")



# Inputs the entered word into the global main_word_list array.
def enter_word_to_main_list():
    global main_word_list
    word = input("Please enter the word that you wish to use accross the software")
    main_word_list = string_to_list(word.lower())
    print("The word you have entered is", word)


# This function checks if the word is palidrome.
def isPalidrome():
    global main_word_list

    # This checks if the word entered on option one is equal to the same word but reversed.
    # If it is, then it is palidrome.
    if list_to_string(reverse_list(main_word_list)) == list_to_string(main_word_list):
        print("The word", list_to_string(main_word_list), "is Palidrome")
        return True

    else:
        print("The word", list_to_string(main_word_list), "is not a Palidrome word")
        return False


# This function checks if the word is anagram. It works by placing the word entered in option 1 in alphabetical order,
# and then invites the user to enter a second word that will sort it too.
def isAnagram():
    global main_word_list

    # Inviting the user to enter the word that they want to check.
    second_word = input("Please enter a second word that you want to check if it is anagram: ")

    # Making the entered word into a list.
    second_word = string_to_list(second_word)


    # This checks if the word in option one, that has been sorted as a string in an alphabetical order is equal to
    # the word that the user was invited to write. For example, if ABCD == ABCD then result = true;
    if list_to_string(place_list_in_order(main_word_list)) == list_to_string(place_list_in_order(second_word)):
        print("The word", list_to_string(second_word), "is an Anagram word")

    else:
        print("The word", list_to_string(second_word), "is not an Anagram word")


# This is what runs the entire program.
main()