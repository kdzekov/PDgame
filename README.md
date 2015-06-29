# PDgame
Multi-agent prisoner's dilemma game written on ZMQ web socket protocol.

In a few words, this project is about multi-agent prisoner's dilemma game using web socket protocol. 
The programme is
written in Php5. The socket protocol is Ratchet/ZMQ, written also in php. There are three important files regarding the
software:

- bin/PDG_server.php, standalone server engine that calls the server side script that handles client actions like: connections
  and choices of the players.

- src/MyApp/PDG.php, the server side script. Here are defined all the events related to the communication protocol like:
OnOpen, OnMesage, OnClose etc. This script manages the workflow of the game. It controls the synchronization of the players,
generates random pair-players, handles the client actions, connections and records the results of the game.

- PDG_client_.html, client interface of the game.

- client_admin_form.php, admin side. Through this module can be controlled some game parameters like: the payoff matrix, 
the number of rounds, the number of players, the game type (pure/mixed), etc.
