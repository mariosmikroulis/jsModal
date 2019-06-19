package com.l2jfrozen.gameserver.model.actor.instance;

import com.l2jfrozen.crypt.Base64;
import com.l2jfrozen.gameserver.ai.CtrlIntention;
import com.l2jfrozen.gameserver.model.L2Character;
import com.l2jfrozen.gameserver.network.serverpackets.ActionFailed;
import com.l2jfrozen.gameserver.network.serverpackets.LeaveWorld;
import com.l2jfrozen.gameserver.network.serverpackets.MyTargetSelected;
import com.l2jfrozen.gameserver.network.serverpackets.NpcHtmlMessage;
import com.l2jfrozen.gameserver.network.serverpackets.ValidateLocation;
import com.l2jfrozen.gameserver.templates.L2NpcTemplate;
import com.l2jfrozen.util.database.L2DatabaseFactory;

import java.security.MessageDigest;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.StringTokenizer;

import org.apache.log4j.Logger;

import javolution.text.TextBuilder;

public class L2PasswordChangerInstance extends L2FolkInstance
{
	protected static final Logger _log = Logger.getLogger(L2Character.class.getName());
 public L2PasswordChangerInstance(int objectId, L2NpcTemplate template)
 {
 super(objectId, template);
 }

 @Override
public void onBypassFeedback(L2PcInstance player, String command)
 {
 if (command.startsWith("change_password"))
 {
 StringTokenizer st = new StringTokenizer(command);
 st.nextToken();
 String currPass = null;
 String newPass = null;
 String repeatNewPass = null;
 try
 {
 if (st.hasMoreTokens())
 {
 currPass = st.nextToken();
 newPass = st.nextToken();
 repeatNewPass = st.nextToken();
 }
 else
 {
 player.sendMessage("Please fill in all the blanks before requesting for a password change.");
 return;
 }
 changePassword(currPass, newPass, repeatNewPass, player);
 }
 catch (StringIndexOutOfBoundsException e)
 {
 }
 }
 }

 @Override
public void onAction(L2PcInstance player)
 {
 if (!canTarget(player)) {
 return;
 }

 if (this != player.getTarget())
 {
 player.setTarget(this);

 player.sendPacket(new MyTargetSelected(getObjectId(), 0));

 player.sendPacket(new ValidateLocation(this));
 }
 else if (!canInteract(player))
 {
 player.getAI().setIntention(CtrlIntention.AI_INTENTION_INTERACT, this);
 }
 else
 {
 showHtmlWindow(player);
 }

 player.sendPacket(new ActionFailed());
 }

 private void showHtmlWindow(L2PcInstance activeChar)
 {
 NpcHtmlMessage nhm = new NpcHtmlMessage(5);
 TextBuilder replyMSG = new TextBuilder("");

 replyMSG.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\">___________________________________________</font><img src=L2Submission.passwordchanger height=90 width=256><br1><font color=\"003366\" align=\"center\">___________________________________________</font><br1>");
 replyMSG.append("To change your password:<br1> First fill in your current password and then your new!</font><br>");
 replyMSG.append("Current Password: <edit var=\"cur\" width=100 height=15><br>");
 replyMSG.append("New Password: <edit var=\"new\" width=100 height=15><br>");
 replyMSG.append("Repeat New Password: <edit var=\"repeatnew\" width=100 height=15><br><br>");
 replyMSG.append("<button value=\"Change Password\" action=\"bypass -h npc_" + getObjectId() + "_change_password $cur $new $repeatnew\" width=204 height=20 back=\"L2Submssion.button\" fore=\"L2Submission.button1\">");
 replyMSG.append("<font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");

 nhm.setHtml(replyMSG.toString());
 activeChar.sendPacket(nhm);

 activeChar.sendPacket(new ActionFailed());
 }

 @SuppressWarnings({
	"null"
})
public static boolean changePassword(String currPass, String newPass, String repeatNewPass, L2PcInstance activeChar)
 {
 if (newPass.length() < 5)
 {
 activeChar.sendMessage("The new password is too short!");
 return false;
 }
 if (newPass.length() > 20)
 {
 activeChar.sendMessage("The new password is too long!");
 return false;
 }
 if (!newPass.equals(repeatNewPass))
 {
 activeChar.sendMessage("Repeated password doesn't match the new password.");
 return false;
 }

 Connection con = null;
 String password = null;
 try
 {
 MessageDigest md = MessageDigest.getInstance("SHA");
 byte[] raw = currPass.getBytes("UTF-8");
 raw = md.digest(raw);
 String currPassEncoded = Base64.encodeBytes(raw);

 con = L2DatabaseFactory.getInstance().getConnection();
 PreparedStatement statement = con.prepareStatement("SELECT password FROM accounts WHERE login=?");
 statement.setString(1, activeChar.getAccountName());
 ResultSet rset = statement.executeQuery();
 while (rset.next())
 {
 password = rset.getString("password");
 }
 rset.close();
 statement.close();
 byte[] password2 =
 null;
 if (currPassEncoded.equals(password))
 {
 password2 = newPass.getBytes("UTF-8");
 password2 = md.digest(password2);

 PreparedStatement statement2 = con.prepareStatement("UPDATE accounts SET password=? WHERE login=?");
 statement2.setString(1, Base64.encodeBytes(password2));
 statement2.setString(2, activeChar.getAccountName());
 statement2.executeUpdate();
 statement2.close();

 activeChar.sendMessage("Congratulations! Your password has been changed succesfully. You will now be disconnected for security reasons. Please login again!");
 try
 {
 Thread.sleep(3000L);
 }
 catch (Exception e)
 {
 }

 activeChar.deleteMe();

 activeChar.sendPacket(new LeaveWorld());
 }
 else
 {
 activeChar.sendMessage("The current password you've inserted is incorrect! Please try again!");

 return password2 != null;
 }
 }
 catch (Exception e)
 {
 _log.warn("could not update the password of account: " + activeChar.getAccountName());
 }
 finally
 {
 try
 {
 if (con != null)
 con.close();
 }
 catch (SQLException e)
 {
 _log.warn("Failed to close database connection!");
 }

 }

 return true;
 }
}