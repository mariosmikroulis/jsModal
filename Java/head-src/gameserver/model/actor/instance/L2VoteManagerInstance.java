package com.l2jfrozen.gameserver.model.actor.instance;

import com.l2jfrozen.Config;
import com.l2jfrozen.gameserver.ai.CtrlIntention;
import com.l2jfrozen.gameserver.datatables.sql.ItemTable;
import com.l2jfrozen.gameserver.model.votereward.VoteMain;
import com.l2jfrozen.gameserver.network.serverpackets.ActionFailed;
import com.l2jfrozen.gameserver.network.serverpackets.ItemList;
import com.l2jfrozen.gameserver.network.serverpackets.MyTargetSelected;
import com.l2jfrozen.gameserver.network.serverpackets.NpcHtmlMessage;
import com.l2jfrozen.gameserver.network.serverpackets.ValidateLocation;
import com.l2jfrozen.gameserver.templates.L2NpcTemplate;

import javolution.text.TextBuilder;

public class L2VoteManagerInstance
  extends L2FolkInstance
{
  public L2VoteManagerInstance(int objectId, L2NpcTemplate template)
  {
    super(objectId, template);
  }
  
  @Override
public void onBypassFeedback(L2PcInstance player, String command)
  {
    if (player == null) {
      return;
    }
    if (command.startsWith("votehopzone"))
    {
      VoteMain.hopvote(player);
    }
    if (command.startsWith("votetopzone"))
    {
      VoteMain.topvote(player);
    }
    if ((command.startsWith("rewards")) && VoteMain.hasVotedHop() && VoteMain.hasVotedTop())
    {
      showRewardsHtml(player);
    }
    
    if ((command.startsWith("reward1")) && VoteMain.hasVotedHop() && VoteMain.hasVotedTop())
    {
      player.getInventory().addItem("reward", Config.VOTE_REWARD_ID1, Config.VOTE_REWARD_AMOUNT1, player, null);
      player.sendMessage("Thanks you for votes. Take your reward.");
      player.sendPacket(new ItemList(player, true));
      VoteMain.setHasNotVotedHop(player);
      VoteMain.setHasNotVotedTop(player);
      VoteMain.setTries(player, VoteMain.getTries(player) + 1);
    }
    
    if ((command.startsWith("reward2")) && VoteMain.hasVotedHop() && VoteMain.hasVotedTop())
    {
      player.getInventory().addItem("reward", Config.VOTE_REWARD_ID2, Config.VOTE_REWARD_AMOUNT2, player, null);
      player.sendMessage("Thanks you for votes. Take your reward.");
      player.sendPacket(new ItemList(player, true));
      VoteMain.setHasNotVotedHop(player);
      VoteMain.setHasNotVotedTop(player);
      VoteMain.setTries(player, VoteMain.getTries(player) + 1);
    }
    
    if ((command.startsWith("reward3")) && VoteMain.hasVotedHop() && VoteMain.hasVotedTop())
    {
      player.getInventory().addItem("reward", Config.VOTE_REWARD_ID3, Config.VOTE_REWARD_AMOUNT3, player, null);
      player.sendMessage("Thanks you for votes. Take your reward.");
      player.sendPacket(new ItemList(player, true));
      VoteMain.setHasNotVotedHop(player);
      VoteMain.setHasNotVotedTop(player);
      VoteMain.setTries(player, VoteMain.getTries(player) + 1);
    }
    
    if ((command.startsWith("reward4")) && VoteMain.hasVotedHop() && VoteMain.hasVotedTop())
    {
      player.getInventory().addItem("reward", Config.VOTE_REWARD_ID4, Config.VOTE_REWARD_AMOUNT4, player, null);
      player.sendMessage("Thanks you for votes. Take your reward.");
      player.sendPacket(new ItemList(player, true));
      VoteMain.setHasNotVotedHop(player);
      VoteMain.setHasNotVotedTop(player);
      VoteMain.setTries(player, VoteMain.getTries(player) + 1);
    }
  }
  
  @Override
public void onAction(L2PcInstance player)
  {
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
    player.sendPacket(ActionFailed.STATIC_PACKET);
  }
  
  public void showHtmlWindow(L2PcInstance activeChar)
  {
    VoteMain.hasVotedHop(activeChar);
    VoteMain.hasVotedTop(activeChar);
    
    TextBuilder tb = new TextBuilder();
    NpcHtmlMessage html = new NpcHtmlMessage(1);
    
    html.setFile("data/html/mods/voteManager.htm");
    
    html.replace("%whoIsVoiting%", String.valueOf(VoteMain.whosVoting()));
    html.replace("%getTries%", String.valueOf(VoteMain.getTries(activeChar)));
    html.replace("%secToVote%", String.valueOf(Config.SECS_TO_VOTE));
    html.replace("%hopCd%", String.valueOf(VoteMain.hopCd(activeChar)));
    html.replace("%topCd%", String.valueOf(VoteMain.hopCd(activeChar)));
    html.replace("%playerTotalVotes%", String.valueOf(VoteMain.getTotalVotes(activeChar)));
    html.replace("%serverTotalVotes%", String.valueOf(VoteMain.getBigTotalVotes(activeChar)));
    
/*    if (Config.VOTE_REWARD_ID1 > 0)
    {
    	html.replace("%voteRewardID1%", String.valueOf("<tr><td align=\"center\"><font color=\"00ffff\">1) " + ItemTable.getInstance().getTemplate(Config.VOTE_REWARD_ID1).getName() + ":</font> " + Config.VOTE_REWARD_AMOUNT1 + "</td></tr>"));
    }
    else if(Config.VOTE_REWARD_ID1==0)
    {
    	html.replace("%voteRewardID1%", String.valueOf(""));
    }
    
    if (Config.VOTE_REWARD_ID2 > 0)
    {
      html.replace("%voteRewardID2%", String.valueOf("<tr><td align=\"center\"><font color=\"00ffff\">2) " + ItemTable.getInstance().getTemplate(Config.VOTE_REWARD_ID2).getName() + ":</font> " + Config.VOTE_REWARD_AMOUNT2 + "</td></tr>"));
    }
    else if(Config.VOTE_REWARD_ID2==0)
    {
    	html.replace("%voteRewardID2%", String.valueOf(""));
    }
    
    if (Config.VOTE_REWARD_ID3 > 0)
    {
    	html.replace("%voteRewardID3%", String.valueOf("<tr><td align=\"center\"><font color=\"00ffff\">3) " + ItemTable.getInstance().getTemplate(Config.VOTE_REWARD_ID3).getName() + ":</font> " + Config.VOTE_REWARD_AMOUNT3 + "</td></tr>"));
    }
    else if(Config.VOTE_REWARD_ID3==0)
    {
    	html.replace("%voteRewardID3%", String.valueOf(""));
    }
    
    if (Config.VOTE_REWARD_ID4 > 0) 
    {
    	html.replace("%voteRewardID4%", String.valueOf("<tr><td align=\"center\"><font color=\"00ffff\">4) " + ItemTable.getInstance().getTemplate(Config.VOTE_REWARD_ID4).getName() + ":</font> " + Config.VOTE_REWARD_AMOUNT4 + "</td></tr>"));
    }
    else if(Config.VOTE_REWARD_ID4==0)
    {
    	html.replace("%voteRewardID4%", String.valueOf(""));
    }*/
    
	if ((VoteMain.hasVotedHop()) && (!VoteMain.hasVotedTop()))
    {
		html.replace("%voteStatus%", String.valueOf("<td><font color = \"00FF00\">For reward you must vote on TOPZONE TOO!</font></td><td><button value=\"Vote Topzone\" action=\"bypass -h npc_" + getObjectId() + "_votetopzone\" width=\"256\" height=\"21\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"></td>"));
    }
    else if ((!VoteMain.hasVotedHop()) && (VoteMain.hasVotedTop()))
    {
    	html.replace("%voteStatus%", String.valueOf("<td><font color = \"00FF00\">For reward you must vote on HOPZONE TOO!</font></td><td><button value=\"Vote Hopzone\" action=\"bypass -h npc_" + getObjectId() + "_votehopzone\" width=\"256\" height=\"21\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"></td>"));
    }
    else if ((!VoteMain.hasVotedHop()) && (!VoteMain.hasVotedTop()))
    {
    	html.replace("%voteStatus%", String.valueOf("<td><button value=\"Vote Hopzone\" action=\"bypass -h npc_" + getObjectId() + "_votehopzone\" width=\"256\" height=\"21\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"></td><td><button value=\"Vote Topzone\" action=\"bypass -h npc_" + getObjectId() + "_votetopzone\" width=\"256\" height=\"21\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"></td>"));
    }
    else
    {
    	html.replace("%voteStatus%", String.valueOf("<td><button value=\"Take The REWARD\" action=\"bypass -h npc_" + getObjectId() + "_rewards\" width=\"256\" height=\"21\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"></td>"));
    }
    if (!VoteMain.hasVotedHop())
    {
    	html.replace("%hopStatus%", String.valueOf("<tr><td align=\"center\"><font color=\"FF6600\">Hopzone Status: </font><font color=\"00FFFF\">NOT VOTED.</font></td></tr>"));
    }
    else
    {
    	html.replace("%hopStatus%", String.valueOf("<tr><td align=\"center\"><font color=\"FF6600\">Hopzone Status: </font><font color=\"FF00FF\">VOTED.</font></td></tr>"));
    }
    if (!VoteMain.hasVotedTop())
    {
    	html.replace("%topStatus%", String.valueOf("<tr><td align=\"center\"><font color=\"FF6600\">Topzone Status: </font><font color=\"00FFFF\">NOT VOTED.</font></td></tr>"));
    }
    else
    {
    	html.replace("%topStatus%", String.valueOf("<tr><td align=\"center\"><font color=\"FF6600\">Topzone Status: </font><font color=\"FF00FF\">VOTED.</font></td></tr>"));
    }
    
    html.setHtml(tb.toString());
    activeChar.sendPacket(html);
  }
  
  public void showRewardsHtml(L2PcInstance player)
  {
    TextBuilder tb = new TextBuilder();
    NpcHtmlMessage html = new NpcHtmlMessage(1);
    
    html.setFile("data/html/mods/voteManagerReward.htm");
    
    html.replace("%playerName%", String.valueOf(player.getName()));
    
    if (Config.VOTE_REWARD_ID1 > 0) {
    	html.replace("%voteRewardID1%", String.valueOf("<button value=\"Item:" + ItemTable.getInstance().getTemplate(Config.VOTE_REWARD_ID1).getName() + "   Amount:" + Config.VOTE_REWARD_AMOUNT1 + "\" action=\"bypass -h npc_" + getObjectId() + "_reward1\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"> width=256 height=21>"));
    }
    else if(Config.VOTE_REWARD_ID1 == 0)
    	html.replace("%voteRewardID1%", String.valueOf(""));
    
    if (Config.VOTE_REWARD_ID2 > 0) {
    	html.replace("%voteRewardID2%", String.valueOf("<button value=\"Item:" + ItemTable.getInstance().getTemplate(Config.VOTE_REWARD_ID2).getName() + "   Amount:" + Config.VOTE_REWARD_AMOUNT2 + "\" action=\"bypass -h npc_" + getObjectId() + "_reward2\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"> width=256 height=21>"));
    }
    else if(Config.VOTE_REWARD_ID2 == 0)
    	html.replace("%voteRewardID2%", String.valueOf(""));
    
    if (Config.VOTE_REWARD_ID3 > 0) {
    	html.replace("%voteRewardID3%", String.valueOf("<button value=\"Item:" + ItemTable.getInstance().getTemplate(Config.VOTE_REWARD_ID3).getName() + "   Amount:" + Config.VOTE_REWARD_AMOUNT3 + "\" action=\"bypass -h npc_" + getObjectId() + "_reward3\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"> width=256 height=21>"));
    }
    else if(Config.VOTE_REWARD_ID3 == 0)
    	html.replace("%voteRewardID3%", String.valueOf(""));
    
    if (Config.VOTE_REWARD_ID4 > 0) {
    	html.replace("%voteRewardID4%", String.valueOf("<button value=\"Item:" + ItemTable.getInstance().getTemplate(Config.VOTE_REWARD_ID4).getName() + "   Amount:" + Config.VOTE_REWARD_AMOUNT4 + "\" action=\"bypass -h npc_" + getObjectId() + "_reward4\" back=\"L2Submission.button2\" fore=\"L2Submission.button1\"> width=256 height=21>"));
    }
    else if(Config.VOTE_REWARD_ID4 == 0)
    	html.replace("%voteRewardID4%", String.valueOf(""));

    html.setHtml(tb.toString());
    player.sendPacket(html);
  }
}
