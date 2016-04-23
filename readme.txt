##############################################
#											 #
#	Swiss Tournament Manager				 #
#	--------------------------------------	 #
#	Made by: 	Thomas Roovers				 #
#	Date:		10-11-2015					 #
#	Version:	1.0							 #
#											 #
##############################################


# 1. First of all
This code is free for use by anyone who has trouble with programming a Swiss Tournament with PHP.
I've written this for use of my own, but thought it might be of some help to others.


# 2. Installation
It's very easy to install the code on your server/localhost. The steps below describe the process of installation.
	
	a. 	Copy the entire directory 'swiss_tournament' to a location of your desire.
	b. 	Create a database which has to be named: 'swiss_tournament'. When you change this name, the code has to be changed as well.
	c. 	Open the directory with your browser. The file 'index.php' will be opened.
	d. 	The file will now check if the database exists. If it does, it will be accessed.
		The next step will be performed automatically. The file creates a table called 'tournament_results',
		in which the results of all the tournaments will be stored for review.
	e. 	You're done!


# 3. How to use
When you open the directory in your browser, it will open the 'index.php' file. 
In that file you can create a new tournament, or view an existing one.

When you've created a tournament, it will be displayed in a table above the form.
You can then click the link and you will be redirected to the page in which the matches are being displayed.
You can also empty or delete the tournament.

Once you've been redirected to the tournament page, the tables needed will be created.
It won't take long and the page will automatically refresh itself. The tables for data storage are created.

You can now add participants by clicking the link in the upper right corner of the window.
By filling in the name of the participant and clicking the submit button, the name will be listed in the table below.
Participants can also be deleted, in case you've made a typo.

Once you've created a list of participants, you can go back to the tournament schedule.
The players will now be matched with an opponent. You can fill in the results of that match and click the submit button below.
The next round will be automatically created after that. Players who have won, will move to the left. 
Players who have lost a match, will move to the right. The system counts the number of matches won, 
to decide where to place the player in the pyramid.

After the final has taken place in the left corner of the pyramid, the system will show you the results of the tournament.
The tournament will end after the final has taken place.


# 4. Questions
If you have any questions regarding the code, you can e-mail me on this address: info@geekk.nl.


### Good luck! ###