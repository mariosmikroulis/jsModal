deviceType = input("Please enter the device type you are using")

print("The device type you are using is ", deviceType, "and it is", len(deviceType), "characters.")

if deviceType.islower():
    print("The entire device type has been entered with lower case letters.")
elif deviceType.isupper():
    print("The entire device type has been entered with upper case letters.")

elif deviceType.isupper() == False and deviceType.islower() == False:
    print("The entered device type has been registered with both Upper and Lower case letters.")

print("Would you like to see the registered type in just upper and lower cases?")
answer = input("If yes, enter yes and for no, enter no.")

if answer == "yes":
    print("How would you like it to be printed?")
    print("Type upper for upper case letters,", "type lower for lower case letters", "and for all , type any")
    upper_lower_both = input("How would you like it to be printed?")
    if upper_lower_both == "":
        print(deviceType.upper(), ",", deviceType.lower())


