/*
 * L2jFrozen Project - www.l2jfrozen.com 
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 *
 * http://www.gnu.org/copyleft/gpl.html
 */
package com.l2jfrozen.gameserver.network.clientpackets;

import org.apache.log4j.Logger;

import com.l2jfrozen.Config;
import com.l2jfrozen.gameserver.ai.CtrlIntention;
import com.l2jfrozen.gameserver.communitybbs.CommunityBoard;
import com.l2jfrozen.gameserver.datatables.sql.AdminCommandAccessRights;
import com.l2jfrozen.gameserver.handler.AdminCommandHandler;
import com.l2jfrozen.gameserver.handler.IAdminCommandHandler;
import com.l2jfrozen.gameserver.handler.custom.CustomBypassHandler;
import com.l2jfrozen.gameserver.managers.CastleManager;
import com.l2jfrozen.gameserver.model.L2Object;
import com.l2jfrozen.gameserver.model.L2World;
import com.l2jfrozen.gameserver.model.actor.instance.L2ClassMasterInstance;
import com.l2jfrozen.gameserver.model.actor.instance.L2NpcInstance;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.model.actor.instance.L2SymbolMakerInstance;
import com.l2jfrozen.gameserver.model.actor.instance.L2TeleporterInstance;
import com.l2jfrozen.gameserver.model.actor.position.L2CharPosition;
import com.l2jfrozen.gameserver.model.entity.event.CTF;
import com.l2jfrozen.gameserver.model.entity.event.DM;
import com.l2jfrozen.gameserver.model.entity.event.L2Event;
import com.l2jfrozen.gameserver.model.entity.event.TvT;
import com.l2jfrozen.gameserver.model.entity.event.VIP;
import com.l2jfrozen.gameserver.model.entity.olympiad.Olympiad;
import com.l2jfrozen.gameserver.model.entity.siege.Castle;
import com.l2jfrozen.gameserver.network.serverpackets.ActionFailed;
import com.l2jfrozen.gameserver.network.serverpackets.NpcHtmlMessage;
import com.l2jfrozen.gameserver.network.serverpackets.SiegeInfo;
import com.l2jfrozen.gameserver.util.GMAudit;
import com.l2jfrozen.gameserver.handler.admincommandhandlers.AdminSurvey;
import com.l2jfrozen.gameserver.network.SystemMessageId;
import com.l2jfrozen.gameserver.network.serverpackets.SystemMessage;
import com.l2jfrozen.gameserver.handler.voicedcommandhandlers.Menu;

public final class RequestBypassToServer extends L2GameClientPacket
{
	private static Logger LOGGER = Logger.getLogger(RequestBypassToServer.class);
	
	// S
	private String _command;
	
	@Override
	protected void readImpl()
	{
		_command = readS();
	}
	
	@Override
	protected void runImpl()
	{
		final L2PcInstance activeChar = getClient().getActiveChar();
		
		if (activeChar == null)
			return;
		
		if (!getClient().getFloodProtectors().getServerBypass().tryPerformAction(_command))
			return;
		
		try
		{
			if (_command.startsWith("admin_"))
			{
				// DaDummy: this way we LOGGER _every_ admincommand with all related info
				String command;
				
				if (_command.contains(" "))
				{
					command = _command.substring(0, _command.indexOf(" "));
				}
				else
				{
					command = _command;
				}
				
				final IAdminCommandHandler ach = AdminCommandHandler.getInstance().getAdminCommandHandler(command);
				
				if (ach == null)
				{
					if (activeChar.isGM())
					{
						activeChar.sendMessage("The command " + command + " does not exists!");
					}
					
					LOGGER.warn("No handler registered for admin command '" + command + "'");
					return;
				}
				
				if (!AdminCommandAccessRights.getInstance().hasAccess(command, activeChar.getAccessLevel()))
				{
					activeChar.sendMessage("You don't have the access right to use this command!");
					if (Config.DEBUG)
					{
						LOGGER.warn("Character " + activeChar.getName() + " tried to use admin command " + command + ", but doesn't have access to it!");
					}
					return;
				}
				
				if (Config.GMAUDIT)
				{
					GMAudit.auditGMAction(activeChar.getName() + " [" + activeChar.getObjectId() + "]", command, (activeChar.getTarget() != null ? activeChar.getTarget().getName() : "no-target"), _command.replace(command, ""));
					
				}
				
				ach.useAdminCommand(_command, activeChar);
			}
			else if (_command.equals("come_here") && activeChar.isGM())
			{
				comeHere(activeChar);
			}
			else if (_command.startsWith("player_help "))
			{
				playerHelp(activeChar, _command.substring(12));
			}
			
			else if (_command.equals("survey_vote1"))
			{
			        if(AdminSurvey.running == false)
			        {
			        	activeChar.sendMessage("Unfortunatelly, there is not any survey that is running now or the surney may finished a while ago.");
			                return;
			        }
			       
			        if(activeChar.hasVotedSurvey())
			        {
			        	activeChar.sendMessage("Sorry, but you have already voted for that survey. Please, wait for the results of this survey!");
			                return;
			        }
			       
			        AdminSurvey.ans1_vote_count++;
			        activeChar.setHasVotedSurvey(true);
			        activeChar.sendMessage("You have voted " + AdminSurvey.ans1 + ". Thank you very much for your vote! Your vote may help us in an important decision or question.");
			}
			
			else if (_command.equals("survey_vote2"))
			{
			        if(AdminSurvey.running == false)
			        {
			        	activeChar.sendMessage("Unfortunatelly, there is not any survey that is running now or the surney may finished a while ago.");
			            return;
			        }
			        if(activeChar.hasVotedSurvey())
			        {
			        	activeChar.sendMessage("Sorry, but you have already voted for that survey. Please, wait for the results of this survey!");
			        	return;
			        }
			       
			        AdminSurvey.ans2_vote_count++;
			        activeChar.setHasVotedSurvey(true);
			        activeChar.sendMessage("You have voted" + AdminSurvey.ans2 + ". Thank you very much for your vote! Your vote may help us in an important decision or question.");
			}
			else if (_command.equals("survey_vote3"))
			{
			        if(AdminSurvey.running == false)
			        {
			        	activeChar.sendMessage("Unfortunatelly, there is not any survey that is running now or the surney may finished a while ago.");
			        	return;
			        }
			        if(activeChar.hasVotedSurvey())
			        {
			        	activeChar.sendMessage("Sorry, but you have already voted for that survey. Please, wait for the results of this survey!");
			        	return;
			        }
			        AdminSurvey.ans3_vote_count++;
			        activeChar.setHasVotedSurvey(true);
			        activeChar.sendMessage("You have voted " + AdminSurvey.ans3 + ". Thank you very much for your vote! Your vote may help us in an important decision or question.");
			}
			else if (_command.equals("survey_vote4"))
			{      
			        if(AdminSurvey.running == false)
			        {
			        	activeChar.sendMessage("Unfortunatelly, there is not any survey that is running now or the surney may finished a while ago.");
			                return;
			        }
			        if(activeChar.hasVotedSurvey())
			        {
			                activeChar.sendMessage("Sorry, but you have already voted for that survey. Please, wait for the results of this survey!");
			                return;
			        }
			        AdminSurvey.ans4_vote_count++;
			        activeChar.setHasVotedSurvey(true);
			        activeChar.sendMessage("You have voted " + AdminSurvey.ans4 + ". Thank you very much for your vote! Your vote may help us in an important decision or question.");
			}
			                      
			else if (_command.equals("survey_vote5"))
			{      
			        if(AdminSurvey.running == false)
			        {
			                activeChar.sendMessage("Unfortunatelly, there is not any survey that is running now or the surney may finished a while ago.");
			                return;
			        }
			        if(activeChar.hasVotedSurvey())
			        {
			        	activeChar.sendMessage("Sorry, but you have already voted for that survey. Please, wait for the results of this survey!");
			        	return;
			        }
			        
			        AdminSurvey.ans5_vote_count++;
			        activeChar.setHasVotedSurvey(true);
			        activeChar.sendMessage("You have voted " + AdminSurvey.ans5 + ". Thank you very much for your vote! Your vote may help us in an important decision or question.");
			}
			
			else if (_command.startsWith("npc_"))
			{
				if (!activeChar.validateBypass(_command))
					return;
				
				final int endOfId = _command.indexOf('_', 5);
				String id;
				
				if (endOfId > 0)
				{
					id = _command.substring(4, endOfId);
				}
				else
				{
					id = _command.substring(4);
				}
				
				try
				{
					final L2Object object = L2World.getInstance().findObject(Integer.parseInt(id));
					
					if (_command.substring(endOfId + 1).startsWith("event_participate"))
					{
						L2Event.inscribePlayer(activeChar);
					}
					else if (_command.substring(endOfId + 1).startsWith("tvt_player_join "))
					{
						final String teamName = _command.substring(endOfId + 1).substring(16);
						
						if (TvT.is_joining())
						{
							TvT.addPlayer(activeChar, teamName);
						}
						else
						{
							activeChar.sendMessage("The event is already started. You can not join now!");
						}
					}
					
					else if (_command.substring(endOfId + 1).startsWith("tvt_player_leave"))
					{
						if (TvT.is_joining())
						{
							TvT.removePlayer(activeChar);
						}
						else
						{
							activeChar.sendMessage("The event is already started. You can not leave now!");
						}
					}
					
					else if (_command.substring(endOfId + 1).startsWith("dmevent_player_join"))
					{
						if (DM.is_joining())
							DM.addPlayer(activeChar);
						else
							activeChar.sendMessage("The event is already started. You can't join now!");
					}
					
					else if (_command.substring(endOfId + 1).startsWith("dmevent_player_leave"))
					{
						if (DM.is_joining())
							DM.removePlayer(activeChar);
						else
							activeChar.sendMessage("The event is already started. You can't leave now!");
					}
					
					else if (_command.substring(endOfId + 1).startsWith("ctf_player_join "))
					{
						final String teamName = _command.substring(endOfId + 1).substring(16);
						if (CTF.is_joining())
							CTF.addPlayer(activeChar, teamName);
						else
							activeChar.sendMessage("The event is already started. You can't join now!");
					}
					
					else if (_command.substring(endOfId + 1).startsWith("ctf_player_leave"))
					{
						if (CTF.is_joining())
							CTF.removePlayer(activeChar);
						else
							activeChar.sendMessage("The event is already started. You can't leave now!");
					}
					
					if (_command.substring(endOfId + 1).startsWith("vip_joinVIPTeam"))
					{
						VIP.addPlayerVIP(activeChar);
					}
					
					if (_command.substring(endOfId + 1).startsWith("vip_joinNotVIPTeam"))
					{
						VIP.addPlayerNotVIP(activeChar);
					}
					
					if (_command.substring(endOfId + 1).startsWith("vip_finishVIP"))
					{
						VIP.vipWin(activeChar);
					}
					
					if (_command.substring(endOfId + 1).startsWith("event_participate"))
					{
						L2Event.inscribePlayer(activeChar);
					}
					
					else if ((Config.ALLOW_CLASS_MASTERS && Config.ALLOW_REMOTE_CLASS_MASTERS && object instanceof L2ClassMasterInstance) || (object instanceof L2NpcInstance && endOfId > 0 && activeChar.isInsideRadius(object, L2NpcInstance.INTERACTION_DISTANCE, false, false)))
					{
						((L2NpcInstance) object).onBypassFeedback(activeChar, _command.substring(endOfId + 1));
					}
					
					activeChar.sendPacket(ActionFailed.STATIC_PACKET);
				}
				catch (final NumberFormatException nfe)
				{
					if (Config.ENABLE_ALL_EXCEPTIONS)
						nfe.printStackTrace();
					
				}
			}
			// Draw a Symbol
			else if (_command.equals("Draw"))
			{
				final L2Object object = activeChar.getTarget();
				if (object instanceof L2NpcInstance)
				{
					((L2SymbolMakerInstance) object).onBypassFeedback(activeChar, _command);
				}
			}
			else if (_command.equals("RemoveList"))
			{
				final L2Object object = activeChar.getTarget();
				if (object instanceof L2NpcInstance)
				{
					((L2SymbolMakerInstance) object).onBypassFeedback(activeChar, _command);
				}
			}
			else if (_command.equals("Remove "))
			{
				final L2Object object = activeChar.getTarget();
				
				if (object instanceof L2NpcInstance)
				{
					((L2SymbolMakerInstance) object).onBypassFeedback(activeChar, _command);
				}
			}
			// Navigate throught Manor windows
			else if (_command.startsWith("manor_menu_select?"))
			{
				final L2Object object = activeChar.getTarget();
				if (object instanceof L2NpcInstance)
				{
					((L2NpcInstance) object).onBypassFeedback(activeChar, _command);
				}
			}
			else if (_command.startsWith("bbs_"))
			{
				CommunityBoard.getInstance().handleCommands(getClient(), _command);
			}
			else if (_command.startsWith("_bbs"))
			{
				CommunityBoard.getInstance().handleCommands(getClient(), _command);
			}
			else if (_command.startsWith("Quest "))
			{
				if (!activeChar.validateBypass(_command))
					return;
				
				final L2PcInstance player = getClient().getActiveChar();
				if (player == null)
					return;
				
				final String p = _command.substring(6).trim();
				final int idx = p.indexOf(' ');
				
				if (idx < 0)
				{
					player.processQuestEvent(p, "");
				}
				else
				{
					player.processQuestEvent(p.substring(0, idx), p.substring(idx).trim());
				}
			}
			
			else if (_command.startsWith("page1"))
				Menu.mainHtml(activeChar);
			else if (_command.startsWith("buffprot"))
			{
				if (activeChar.isBuffProtected())
				{
					activeChar.setIsBuffProtected(false);
					activeChar.sendMessage("Buff protection is disabled.");
					Menu.mainHtml(activeChar);
				}
				else
				{
					activeChar.setIsBuffProtected(true);
					activeChar.sendMessage("Buff protection is enabled.");
					Menu.mainHtml(activeChar);
				}
			}
			else if (_command.startsWith("tradeprot"))
			{
				boolean isCharNewbie = false;
				
				if (Config.TRADE_DROP_PICK_BUY_SELL_ENABLED && Config.TRADE_TIME_DISABLE>0 && activeChar.getOnlineTime()<Config.TRADE_TIME_DISABLE)
				{
						isCharNewbie = true;
				}
				else
					isCharNewbie = false;
				
				if (activeChar.isInTradeProt() && !isCharNewbie)
				{
					activeChar.setIsInTradeProt(false);
					activeChar.sendMessage("Trade acceptance mode is enabled.");
					Menu.mainHtml(activeChar);
				}
				else if (!activeChar.isInTradeProt() && !isCharNewbie)
				{
					activeChar.setIsInTradeProt(true);
					activeChar.sendMessage("Trade refusal mode is enabled.");
					Menu.mainHtml(activeChar);
				}
				else if (isCharNewbie)
				{
					activeChar.sendMessage("You are in Trade Protection Mode for the next " + Config.TRADE_TIME_TEXT + " since you have created this char. We apologise for that but it is kind of our own protection. If you believe that you are longer than that time, please restart your character now!");
					Menu.mainHtml(activeChar);
				}
			}
			else if (_command.startsWith("ssprot"))
			{
				if (activeChar.isSSDisabled())
				{
					activeChar.setIsSSDisabled(false);
					activeChar.sendMessage("Soulshots effects are enabled.");
					Menu.mainHtml(activeChar);
				}
				else
				{
					activeChar.setIsSSDisabled(true);
					activeChar.sendMessage("Soulshots effects are disabled.");
					Menu.mainHtml(activeChar);
				}
			}
			else if (_command.startsWith("xpnot"))
			{
				if (activeChar.cantGainXP())
				{
					activeChar.cantGainXP(false);
					activeChar.sendMessage("Enable Xp");
					Menu.mainHtml(activeChar);
				}
				else
				{
					activeChar.cantGainXP(true);
					activeChar.sendMessage("Disable Xp");
					Menu.mainHtml(activeChar);
				}
			}
			
			else if (_command.startsWith("pmref"))
			{
				if (activeChar.getMessageRefusal())	
				{
					activeChar.setMessageRefusal(false);
					activeChar.sendPacket(new SystemMessage(SystemMessageId.MESSAGE_ACCEPTANCE_MODE));
					Menu.mainHtml(activeChar);
				}
				else
				{
					activeChar.setMessageRefusal(true);
					activeChar.sendPacket(new SystemMessage(SystemMessageId.MESSAGE_REFUSAL_MODE));
					Menu.mainHtml(activeChar);
				}
			}
			else if (_command.startsWith("partyin"))
			{
				if (activeChar.isPartyInvProt())
				{
					activeChar.setIsPartyInvProt(false);
					activeChar.sendMessage("Party acceptance mode is enabled.");
					Menu.mainHtml(activeChar);
				}
				else
				{
					activeChar.setIsPartyInvProt(true);
					activeChar.sendMessage("Party refusal mode is enabled.");
					Menu.mainHtml(activeChar);
				}
			}
			
			else if (_command.startsWith("partyin"))
			{
				if (activeChar.isPartyInvProt())
				{
					activeChar.setIsPartyInvProt(false);
					activeChar.sendMessage("Party acceptance mode is enabled.");
					Menu.mainHtml(activeChar);
				}
				else
				{
					activeChar.setIsPartyInvProt(true);
					activeChar.sendMessage("Party refusal mode is enabled.");
					Menu.mainHtml(activeChar);
				}
			}
			
			else if (_command.startsWith("page2"))
				Menu.mainHtml(activeChar);
			
			
			// Jstar's Custom Bypass Caller!
			else if (_command.startsWith("custom_"))
			{
				final L2PcInstance player = getClient().getActiveChar();
				CustomBypassHandler.getInstance().handleBypass(player, _command);
			}
			else if (_command.startsWith("OlympiadArenaChange"))
			{
				Olympiad.bypassChangeArena(_command, activeChar);
			}
			else if (_command.startsWith("siege_")) {
                 int castleId = 0;
                
                 if (_command.startsWith("siege_gludio"))
                         castleId = 1;
                 else if (_command.startsWith("siege_dion"))
                         castleId = 2;
                 else if (_command.startsWith("siege_giran"))
                         castleId = 3;
                 else if (_command.startsWith("siege_oren"))
                         castleId = 4;
                 else if (_command.startsWith("siege_aden"))
                         castleId = 5;
                 else if (_command.startsWith("siege_innadril"))
                         castleId = 6;
                 else if (_command.startsWith("siege_goddard"))
                         castleId = 7;
                 else if (_command.startsWith("siege_rune"))
                         castleId = 8;
                 else if (_command.startsWith("siege_schuttgart"))
                         castleId = 9;
               
                Castle castle = CastleManager.getInstance().getCastleById(castleId);
                if(castle != null && castleId != 0)
                        activeChar.sendPacket(new SiegeInfo(castle));
			}
			else if (_command.startsWith("serverinfo"))
			{
				String filename = "";
				if (_command.startsWith("serverinfo_main"))
					filename="6666";
				
				else if (_command.startsWith("serverinfo_generalinfo"))
					filename="6666-1";
		    	   
				else if (_command.startsWith("serverinfo_eventinfo"))
					filename="6666-2";
		    	   
				else if (_command.startsWith("serverinfo_voicecommands"))
					filename="6666-3";
				
				else if (_command.startsWith("serverinfo_contactinfo"))
					filename="6666-4";
				
				else if (_command.startsWith("serverinfo_npcinfo"))
					filename="6666-5";
				
				else if (_command.startsWith("serverinfo_farminfo"))
					filename="6666-6";
				
				else if (_command.startsWith("serverinfo_raidinfo"))
					filename="6666-7";
				
				else if (_command.startsWith("serverinfo_finalinfo"))
					filename="6666-8";
				
				NpcHtmlMessage html = new NpcHtmlMessage(1);
				html.setFile("data/html/merchant/" + filename + ".htm");
				html.replace("%playername%", activeChar.getName());
				activeChar.sendPacket(html);
			}
			
			else if (_command.startsWith("GrandBoss_"))
			{
				if (_command.startsWith("GrandBoss_Valakas"))
				{
					L2TeleporterInstance.doPartyTeleport(activeChar, 67239);
				}
				else if (_command.startsWith("GrandBoss_Antharas"))
				{
					L2TeleporterInstance.doPartyTeleport(activeChar, 67240);
				}
				else if (_command.startsWith("GrandBoss_Baium"))
				{
					L2TeleporterInstance.doPartyTeleport(activeChar, 67241);
				}
				else if (_command.startsWith("GrandBoss_Zaken"))
				{
					L2TeleporterInstance.doPartyTeleport(activeChar, 67242);
				}
				else if (_command.startsWith("GrandBoss_QueenAnt"))
				{
					L2TeleporterInstance.doPartyTeleport(activeChar, 67243);
				}
				else if (_command.startsWith("GrandBoss_Frintezza"))
				{
					L2TeleporterInstance.doPartyTeleport(activeChar, 67244);
				}
				else if (_command.startsWith("GrandBoss_Core"))
				{
					L2TeleporterInstance.doPartyTeleport(activeChar, 67245);
				}
				else if (_command.startsWith("GrandBoss_Orfen"))
				{
					L2TeleporterInstance.doPartyTeleport(activeChar, 67246);
				}
			}
		}
		catch (final Exception e)
		{
			if (Config.ENABLE_ALL_EXCEPTIONS)
				e.printStackTrace();
			
			LOGGER.warn("Bad RequestBypassToServer: ", e);
		}
		// finally
		// {
		// activeChar.clearBypass();
		// }
	}
	
	/**
	 * @param activeChar
	 */
	private void comeHere(final L2PcInstance activeChar)
	{
		final L2Object obj = activeChar.getTarget();
		if (obj == null)
			return;
		
		if (obj instanceof L2NpcInstance)
		{
			final L2NpcInstance temp = (L2NpcInstance) obj;
			temp.setTarget(activeChar);
			temp.getAI().setIntention(CtrlIntention.AI_INTENTION_MOVE_TO, new L2CharPosition(activeChar.getX(), activeChar.getY(), activeChar.getZ(), 0));
			// temp.moveTo(player.getX(),player.getY(), player.getZ(), 0 );
		}
		
	}
	
	private void playerHelp(final L2PcInstance activeChar, final String path)
	{
		if (path.contains(".."))
			return;
		
		final String filename = "data/html/help/" + path;
		final NpcHtmlMessage html = new NpcHtmlMessage(1);
		html.setFile(filename);
		activeChar.sendPacket(html);
	}
	
	@Override
	public String getType()
	{
		return "[C] 21 RequestBypassToServer";
	}
}
