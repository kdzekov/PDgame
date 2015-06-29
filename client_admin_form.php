<?php

    session_start();
    if(isset($_SESSION['post']) && count($_SESSION['post'])) 
    { 
        $_POST = $_SESSION['post'];   
    }
    
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>TODO supply a title</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" type="text/css" href="style.css"> 
        <!-- <script src="assets/js/autobahn.js"></script> -->
    </head>
    <body>
        <form action="client_admin.php" method="post" name="admin">
            
            
            <table id='admin_table' align="center">
                <thead>
                    <tr>
                        <th colspan="6">Prisoner's Dilemma Admin's Page</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="height: 50px">
                        <td colspan="6"></td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="3" style="text-align: left">Payoff matrix:</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="width: 50px"></td>
                        
                        <!--<td><input type="submit" name="setup" id="setup" class="admin_button" value="SETUP"></td>-->
                        <td style="text-align: left"> R:&nbsp;<input type="text" name='r_pff' value="<?php if (isset($_POST['r_pff'])) echo $_POST['r_pff']; ?>" class='payoff_txt'> </td>
                        <td style="width: 10px"></td>
                        <td style="text-align: left"> S:&nbsp;<input type="text" name='s_pff' value="<?php if (isset($_POST['s_pff'])) echo $_POST['s_pff']; ?>" class='payoff_txt'></td>
                        
                    </tr>
                   <tr>
                       <td colspan="3"></td>
                        <!--<td><input type="submit" name="start" class="admin_button" value="START"></td>-->
                        <td style="text-align: left">T:&nbsp;<input type="text" name='t_pff' value="<?php if (isset($_POST['t_pff'])) echo $_POST['t_pff']; ?>" class='payoff_txt'></td>
                        <td></td>
                        <td style="text-align: left">P:&nbsp;<input type="text" name='p_pff' value="<?php if (isset($_POST['p_pff'])) echo $_POST['p_pff']; ?>" class='payoff_txt'></td>
                        
                    </tr>
                    <tr>
                        <td colspan="6" style="height: 20px"></td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td style="text-align: left">Rounds:</td>
                        <td></td>
                        <td style="text-align: left">Players:</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td style="text-align: left"><input type="text" name="rounds" value="<?php if (isset($_POST['rounds'])) echo $_POST['rounds']; ?>" class="admin_txt"></td>
                        <td></td>
                        <td style="text-align: left"><input type="text" name="players" value="<?php if (isset($_POST['players'])) echo $_POST['players']; ?>" class="admin_txt"></td>
                    </tr>
                    <tr>
                        <td colspan="6" style="height: 20px"></td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td style="text-align: left">Game Type:</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td style="text-align: left">
                            <select name="game_type">
                                <option value="1">Pure</option>
                                <option value="2">Mixed</option>
                            </select>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" style="height: 15px"></td>
                    </tr>
                    <tr>
                         <td colspan="3"></td>
                         <td><input type="submit" name="setup" id="setup" class="admin_button" value="SETUP"></td>
                         <td colspan="2"><input type="submit" name="start" class="admin_button" value="START"></td>
                    </tr>
                </tbody>
            </table>
        </form>
    </body>
</html>
