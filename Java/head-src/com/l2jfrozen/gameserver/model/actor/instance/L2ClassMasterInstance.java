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
package com.l2jfrozen.gameserver.model.actor.instance;

import javolution.text.TextBuilder;

import com.l2jfrozen.Config;
import com.l2jfrozen.gameserver.ai.CtrlIntention;
import com.l2jfrozen.gameserver.datatables.sql.CharTemplateTable;
import com.l2jfrozen.gameserver.datatables.sql.ItemTable;
import com.l2jfrozen.gameserver.model.base.ClassId;
import com.l2jfrozen.gameserver.model.base.ClassLevel;
import com.l2jfrozen.gameserver.model.base.PlayerClass;
import com.l2jfrozen.gameserver.model.quest.Quest;
import com.l2jfrozen.gameserver.network.SystemMessageId;
import com.l2jfrozen.gameserver.network.serverpackets.ActionFailed;
import com.l2jfrozen.gameserver.network.serverpackets.MyTargetSelected;
import com.l2jfrozen.gameserver.network.serverpackets.NpcHtmlMessage;
import com.l2jfrozen.gameserver.network.serverpackets.SystemMessage;
import com.l2jfrozen.gameserver.network.serverpackets.ValidateLocation;
import com.l2jfrozen.gameserver.templates.L2NpcTemplate;

/**
 * This class ...
 * @version $Revision: 1.4.2.1.2.7 $ $Date: 2005/03/27 15:29:32 $
 */
public final class L2ClassMasterInstance extends L2FolkInstance
{
	
	/** The _instance. */
	private static L2ClassMasterInstance _instance;
	
	/**
	 * Instantiates a new l2 class master instance.
	 * @param objectId the object id
	 * @param template the template
	 */
	public L2ClassMasterInstance(final int objectId, final L2NpcTemplate template)
	{
		super(objectId, template);
		_instance = this;
	}
	
	/**
	 * Gets the single instance of L2ClassMasterInstance.
	 * @return single instance of L2ClassMasterInstance
	 */
	public static L2ClassMasterInstance getInstance()
	{
		
		return _instance;
		
	}
	
	/*
	 * (non-Javadoc)
	 * @see com.l2jfrozen.gameserver.model.actor.instance.L2FolkInstance#onAction(com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance)
	 */
	@Override
	public void onAction(final L2PcInstance player)
	{
		if (Config.DEBUG)
		{
			LOGGER.info("Class master activated!");
		}
		
		player.setLastFolkNPC(this);
		
		// Check if the L2PcInstance already target the L2NpcInstance
		if (this != player.getTarget() && !Config.ALLOW_REMOTE_CLASS_MASTERS)
		{
			// Set the target of the L2PcInstance player
			player.setTarget(this);
			
			// Send a Server->Client packet MyTargetSelected to the L2PcInstance player
			MyTargetSelected my = new MyTargetSelected(getObjectId(), 0);
			player.sendPacket(my);
			my = null;
			
			// Send a Server->Client packet ValidateLocation to correct the L2NpcInstance position and heading on the client
			player.sendPacket(new ValidateLocation(this));
		}
		else
		{
			// Calculate the distance between the L2PcInstance and the L2NpcInstance
			if (!canInteract(player) && !Config.ALLOW_REMOTE_CLASS_MASTERS)
			{
				// Notify the L2PcInstance AI with AI_INTENTION_INTERACT
				player.getAI().setIntention(CtrlIntention.AI_INTENTION_INTERACT, this);
			}
			else
			{
				ClassId classId = player.getClassId();
				int jobLevel = 0;
				final int level = player.getLevel();
				ClassLevel lvl = PlayerClass.values()[classId.getId()].getLevel();
				switch (lvl)
				{
					case First:
						jobLevel = 1;
						break;
					case Second:
						jobLevel = 2;
						break;
					case Third:
						jobLevel = 3;
						break;
					default:
						jobLevel = 4;
				}
				if (player.isAio() && !Config.ALLOW_AIO_USE_CM)
				{
					player.sendMessage("Aio Buffers Can't Speak To Class Masters.");
					return;
				}
				if (player.isGM())
				{
					showChatWindowChooseClass(player);
				}
				else if (level >= 20 && jobLevel == 1 && Config.ALLOW_CLASS_MASTERS_FIRST_CLASS || level >= 40 && jobLevel == 2 && Config.ALLOW_CLASS_MASTERS_SECOND_CLASS || level >= 76 && jobLevel == 3 && Config.ALLOW_CLASS_MASTERS_THIRD_CLASS || Config.CLASS_MASTER_STRIDER_UPDATE)
				{
					final NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
					final TextBuilder sb = new TextBuilder();
					sb.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\">___________________________________________</font><img src=L2Submission.classmanager height=90 width=256><br1><font color=\"003366\" align=\"center\">___________________________________________</font></center></center><br1>");
					
					if (level >= 20 && jobLevel == 1 && Config.ALLOW_CLASS_MASTERS_FIRST_CLASS || level >= 40 && jobLevel == 2 && Config.ALLOW_CLASS_MASTERS_SECOND_CLASS || level >= 76 && jobLevel == 3 && Config.ALLOW_CLASS_MASTERS_THIRD_CLASS)
					{
						sb.append("<font color=AAAAAA>Please choose from the list of classes below...</font><br><br>");
						
						for (final ClassId child : ClassId.values())
							if (child.childOf(classId) && child.level() == jobLevel)
							{
								sb.append("<br><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class " + child.getId() + "\" value=\" " + CharTemplateTable.getClassNameById(child.getId()) + "\">");
							}
						
						if (Config.CLASS_MASTER_SETTINGS.getRequireItems(jobLevel) != null && Config.CLASS_MASTER_SETTINGS.getRequireItems(jobLevel).size() > 0)
						{
							sb.append("<br><br>Item(s) required for class change:");
							sb.append("<table width=220>");
							for (final Integer _itemId : Config.CLASS_MASTER_SETTINGS.getRequireItems(jobLevel).keySet())
							{
								final int _count = Config.CLASS_MASTER_SETTINGS.getRequireItems(jobLevel).get(_itemId);
								sb.append("<tr><td><font color=\"LEVEL\" value=\"" + _count + "</font></td><td>" + ItemTable.getInstance().getTemplate(_itemId).getName() + "</td></tr>");
							}
							sb.append("</table>");
						}
					}
					if (Config.CLASS_MASTER_STRIDER_UPDATE)
					{
						sb.append("<br><br><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_upgrade_hatchling\" value=\"Upgrade Hatchling to Strider\"><br>");
					}
					sb.append("<br><font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");
					html.setHtml(sb.toString());
					player.sendPacket(html);
				}
				else
				{
					final NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
					final TextBuilder sb = new TextBuilder();
					sb.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\">___________________________________________</font><img src=L2Submission.classmanager height=90 width=256><br1><font color=\"003366\" align=\"center\">___________________________________________</font></center><br1>");
					switch (jobLevel)
					{
						case 1:
							sb.append("Come back here when you reach level 20 to change your class.<br>");
							break;
						case 2:
							sb.append("Come back here when you reach level 40 to change your class.<br>");
							break;
						case 3:
							sb.append("Come back here when you reach level 76 to change your class.<br>");
							break;
						case 4:
							sb.append("There are no more class changes for you.<br>");
							break;
					}
					if (Config.CLASS_MASTER_STRIDER_UPDATE)
					{
						sb.append("<br><br><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_upgrade_hatchling\" value=\"Upgrade Hatchling to Strider\"><br>");
					}
					for (final Quest q : Quest.findAllEvents())
					{
						sb.append("Event: <button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h Quest " + q.getName() + "\" value=\"" + q.getDescr() + "\"><br>");
					}
					sb.append("<br><font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");
					html.setHtml(sb.toString());
					player.sendPacket(html);
				}
				lvl = null;
				classId = null;
			}
		}
		player.sendPacket(ActionFailed.STATIC_PACKET);
	}
	
	/*
	 * (non-Javadoc)
	 * @see com.l2jfrozen.gameserver.model.actor.instance.L2FolkInstance#onBypassFeedback(com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance, java.lang.String)
	 */
	@Override
	public void onBypassFeedback(final L2PcInstance player, final String command)
	{
		if (command.startsWith("1stClass"))
		{
			if (player.isGM())
			{
				showChatWindow1st(player);
			}
		}
		else if (command.startsWith("2ndClass"))
		{
			if (player.isGM())
			{
				showChatWindow2nd(player);
			}
		}
		else if (command.startsWith("3rdClass"))
		{
			if (player.isGM())
			{
				showChatWindow3rd(player);
			}
		}
		else if (command.startsWith("baseClass"))
		{
			if (player.isGM())
			{
				showChatWindowBase(player);
			}
		}
		else if (command.startsWith("change_class"))
		{
			final int val = Integer.parseInt(command.substring(13));
			
			// Exploit prevention
			ClassId classId = player.getClassId();
			final int level = player.getLevel();
			int jobLevel = 0;
			int newJobLevel = 0;
			
			player.setTarget(player);
			
			ClassLevel lvlnow = PlayerClass.values()[classId.getId()].getLevel();
			
			if (player.isGM())
			{
				changeClass(player, val);
				player.rewardSkills();
				
				if (val >= 88)
				{
					player.sendPacket(new SystemMessage(SystemMessageId.THIRD_CLASS_TRANSFER)); // system sound 3rd occupation
				}
				else
				{
					player.sendPacket(new SystemMessage(SystemMessageId.CLASS_TRANSFER)); // system sound for 1st and 2nd occupation
				}
				
				NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
				TextBuilder sb = new TextBuilder();
				sb.append("<html><title>L2Submission!</title><body><br><font color=\"003366\" align=\"center\">___________________________________________</font><center><img src=L2Submission.classmanager height=90 width=256></center><br1><font color=\"003366\" align=\"center\">___________________________________________</font><br1>");
				sb.append("<font align=\"left\">You have now become a <font color=\"LEVEL\" value=\"" + CharTemplateTable.getClassNameById(player.getClassId().getId()) + "</font>.</font>");
				sb.append("<center><br><font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");
				
				html.setHtml(sb.toString());
				player.sendPacket(html);
				html = null;
				sb = null;
				return;
			}
			switch (lvlnow)
			{
				case First:
					jobLevel = 1;
					break;
				case Second:
					jobLevel = 2;
					break;
				case Third:
					jobLevel = 3;
					break;
				default:
					jobLevel = 4;
			}
			
			if (jobLevel == 4)
				return; // no more job changes
				
			ClassLevel lvlnext = PlayerClass.values()[val].getLevel();
			switch (lvlnext)
			{
				case First:
					newJobLevel = 1;
					break;
				case Second:
					newJobLevel = 2;
					break;
				case Third:
					newJobLevel = 3;
					break;
				default:
					newJobLevel = 4;
			}
			
			lvlnext = null;
			// prevents changing between same level jobs
			if (newJobLevel != jobLevel + 1)
				return;
			
			if (level < 20 && newJobLevel > 1)
				return;
			if (level < 40 && newJobLevel > 2)
				return;
			if (level < 76 && newJobLevel > 3)
				return;
			// -- prevention ends
			
			if (newJobLevel == 2 && !Config.ALLOW_CLASS_MASTERS_FIRST_CLASS)
				return;
			
			if (newJobLevel == 3 && !Config.ALLOW_CLASS_MASTERS_SECOND_CLASS)
				return;
			
			if (newJobLevel == 4 && !Config.ALLOW_CLASS_MASTERS_THIRD_CLASS)
				return;
			
			// check if player have all required items for class transfer
			for (final Integer _itemId : Config.CLASS_MASTER_SETTINGS.getRequireItems(jobLevel).keySet())
			{
				final int _count = Config.CLASS_MASTER_SETTINGS.getRequireItems(jobLevel).get(_itemId);
				if (player.getInventory().getInventoryItemCount(_itemId, -1) < _count)
				{
					player.sendPacket(new SystemMessage(SystemMessageId.NOT_ENOUGH_ITEMS));
					return;
				}
			}
			
			// get all required items for class transfer
			for (final Integer _itemId : Config.CLASS_MASTER_SETTINGS.getRequireItems(jobLevel).keySet())
			{
				final int _count = Config.CLASS_MASTER_SETTINGS.getRequireItems(jobLevel).get(_itemId);
				player.destroyItemByItemId("ClassMaster", _itemId, _count, player, true);
			}
			
			// reward player with items
			for (final Integer _itemId : Config.CLASS_MASTER_SETTINGS.getRewardItems(jobLevel).keySet())
			{
				final int _count = Config.CLASS_MASTER_SETTINGS.getRewardItems(jobLevel).get(_itemId);
				player.addItem("ClassMaster", _itemId, _count, player, true);
			}
			
			changeClass(player, val);
			
			player.rewardSkills();
			
			// Check player skills
			if (Config.CHECK_SKILLS_ON_ENTER && !Config.ALT_GAME_SKILL_LEARN)
				player.checkAllowedSkills();
			
			if (val >= 88)
			{
				player.sendPacket(new SystemMessage(SystemMessageId.THIRD_CLASS_TRANSFER)); // system sound 3rd occupation
			}
			else
			{
				player.sendPacket(new SystemMessage(SystemMessageId.CLASS_TRANSFER)); // system sound for 1st and 2nd occupation
			}
			
			NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
			TextBuilder sb = new TextBuilder();
			sb.append("<html><title>L2Submission!</title><body><br><font color=\"003366\" align=\"center\">___________________________________________</font><center><img src=L2Submission.classmanager height=90 width=256></center><br1><font color=\"003366\" align=\"center\">___________________________________________</font><br1>");
			sb.append("<font align=\"left\">You have now become a <font color=\"LEVEL\" value=\"" + CharTemplateTable.getClassNameById(player.getClassId().getId()) + "</font>.</font>");
			sb.append("<br><font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");
			
			html.setHtml(sb.toString());
			player.sendPacket(html);
			
			sb = null;
			html = null;
			lvlnow = null;
			classId = null;
		}
		else if (command.startsWith("upgrade_hatchling") && Config.CLASS_MASTER_STRIDER_UPDATE)
		{
			boolean canUpgrade = false;
			if (player.getPet() != null)
			{
				if (player.getPet().getNpcId() == 12311 || player.getPet().getNpcId() == 12312 || player.getPet().getNpcId() == 12313)
				{
					if (player.getPet().getLevel() >= 55)
					{
						canUpgrade = true;
					}
					else
					{
						player.sendMessage("The level of your hatchling is too low to be upgraded.");
					}
				}
				else
				{
					player.sendMessage("You have to summon your hatchling.");
				}
			}
			else
			{
				player.sendMessage("You have to summon your hatchling if you want to upgrade him.");
			}
			
			if (!canUpgrade)
				return;
			
			final int[] hatchCollar =
			{
				3500,
				3501,
				3502
			};
			final int[] striderCollar =
			{
				4422,
				4423,
				4424
			};
			
			for (int i = 0; i < 3; i++)
			{
				final L2ItemInstance collar = player.getInventory().getItemByItemId(hatchCollar[i]);
				
				if (collar != null)
				{
					// Unsummon the hatchling
					player.getPet().unSummon(player);
					player.destroyItem("ClassMaster", collar, player, true);
					player.addItem("ClassMaster", striderCollar[i], 1, player, true);
					
					return;
				}
			}
		}
		else
		{
			super.onBypassFeedback(player, command);
		}
	}
	
	/**
	 * Show chat window choose class.
	 * @param player the player
	 */
	private void showChatWindowChooseClass(final L2PcInstance player)
	{
		NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
		TextBuilder sb = new TextBuilder();
		sb.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\">___________________________________________</font><img src=L2Submission.classmanager height=90 width=256><br1><font color=\"003366\" align=\"center\">___________________________________________</font></center><br1>");
		sb.append("<table width=200>");
		sb.append("<tr><td><center>GM Class Master:</center></td></tr>");
		sb.append("<tr><td><br></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_baseClass\" value=\"Base Classes.\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_1stClass\" value=\"1st Classes.\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_2ndClass\" value=\"2nd Classes.\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_3rdClass\" value=\"3rd Classes.\"></td></tr>");
		sb.append("<tr><td><br></td></tr>");
		sb.append("</table>");
		sb.append("<font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");
		html.setHtml(sb.toString());
		player.sendPacket(html);
		html = null;
		sb = null;
		return;
	}
	
	/**
	 * Show chat window1st.
	 * @param player the player
	 */
	private void showChatWindow1st(final L2PcInstance player)
	{
		NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
		TextBuilder sb = new TextBuilder();
		sb.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\">___________________________________________</font><img src=L2Submission.classmanager height=90 width=256><br1><font color=\"003366\" align=\"center\">___________________________________________</font></center><br1>");
		sb.append("<table width=200>");
		sb.append("<tr><td><center>GM Class Master:</center></td></tr>");
		sb.append("<tr><td><br></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 1\" value=\"Advance to " + CharTemplateTable.getClassNameById(1) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 4\" value=\"Advance to " + CharTemplateTable.getClassNameById(4) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 7\" value=\"Advance to " + CharTemplateTable.getClassNameById(7) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 11\" value=\"Advance to " + CharTemplateTable.getClassNameById(11) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 15\" value=\"Advance to " + CharTemplateTable.getClassNameById(15) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 19\" value=\"Advance to " + CharTemplateTable.getClassNameById(19) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 22\" value=\"Advance to " + CharTemplateTable.getClassNameById(22) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 26\" value=\"Advance to " + CharTemplateTable.getClassNameById(26) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 29\" value=\"Advance to " + CharTemplateTable.getClassNameById(29) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 32\" value=\"Advance to " + CharTemplateTable.getClassNameById(32) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 35\" value=\"Advance to " + CharTemplateTable.getClassNameById(35) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 39\" value=\"Advance to " + CharTemplateTable.getClassNameById(39) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 42\" value=\"Advance to " + CharTemplateTable.getClassNameById(42) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 45\" value=\"Advance to " + CharTemplateTable.getClassNameById(45) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 47\" value=\"Advance to " + CharTemplateTable.getClassNameById(47) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 50\" value=\"Advance to " + CharTemplateTable.getClassNameById(50) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 54\" value=\"Advance to " + CharTemplateTable.getClassNameById(54) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 56\" value=\"Advance to " + CharTemplateTable.getClassNameById(56) + "\"></td></tr>");
		sb.append("</table>");
		sb.append("<font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html></html>");
		html.setHtml(sb.toString());
		player.sendPacket(html);
		html = null;
		sb = null;
		return;
	}
	
	/**
	 * Show chat window2nd.
	 * @param player the player
	 */
	private void showChatWindow2nd(final L2PcInstance player)
	{
		NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
		TextBuilder sb = new TextBuilder();
		sb.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\">___________________________________________</font><img src=L2Submission.classmanager height=90 width=256><br1><font color=\"003366\" align=\"center\">___________________________________________</font></center><br1>");
		sb.append("<table width=200>");
		sb.append("<tr><td><center>GM Class Master:</center></td></tr>");
		sb.append("<tr><td><br></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 2\" value=\"Advance to " + CharTemplateTable.getClassNameById(2) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 3\" value=\"Advance to " + CharTemplateTable.getClassNameById(3) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 5\" value=\"Advance to " + CharTemplateTable.getClassNameById(5) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 6\" value=\"Advance to " + CharTemplateTable.getClassNameById(6) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 8\" value=\"Advance to " + CharTemplateTable.getClassNameById(8) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 9\" value=\"Advance to " + CharTemplateTable.getClassNameById(9) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 12\" value=\"Advance to " + CharTemplateTable.getClassNameById(12) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 13\" value=\"Advance to " + CharTemplateTable.getClassNameById(13) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 14\" value=\"Advance to " + CharTemplateTable.getClassNameById(14) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 16\" value=\"Advance to " + CharTemplateTable.getClassNameById(16) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 17\" value=\"Advance to " + CharTemplateTable.getClassNameById(17) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 20\" value=\"Advance to " + CharTemplateTable.getClassNameById(20) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 21\" value=\"Advance to " + CharTemplateTable.getClassNameById(21) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 23\" value=\"Advance to " + CharTemplateTable.getClassNameById(23) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 24\" value=\"Advance to " + CharTemplateTable.getClassNameById(24) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 27\" value=\"Advance to " + CharTemplateTable.getClassNameById(27) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 28\" value=\"Advance to " + CharTemplateTable.getClassNameById(28) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 30\" value=\"Advance to " + CharTemplateTable.getClassNameById(30) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 33\" value=\"Advance to " + CharTemplateTable.getClassNameById(33) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 34\" value=\"Advance to " + CharTemplateTable.getClassNameById(34) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 36\" value=\"Advance to " + CharTemplateTable.getClassNameById(36) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 37\" value=\"Advance to " + CharTemplateTable.getClassNameById(37) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 40\" value=\"Advance to " + CharTemplateTable.getClassNameById(40) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 41\" value=\"Advance to " + CharTemplateTable.getClassNameById(41) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 43\" value=\"Advance to " + CharTemplateTable.getClassNameById(43) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 46\" value=\"Advance to " + CharTemplateTable.getClassNameById(46) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 48\" value=\"Advance to " + CharTemplateTable.getClassNameById(48) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 51\" value=\"Advance to " + CharTemplateTable.getClassNameById(51) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 52\" value=\"Advance to " + CharTemplateTable.getClassNameById(52) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 55\" value=\"Advance to " + CharTemplateTable.getClassNameById(55) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 57\" value=\"Advance to " + CharTemplateTable.getClassNameById(57) + "\"></td></tr>");
		sb.append("</table>");
		sb.append("<font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");
		html.setHtml(sb.toString());
		player.sendPacket(html);
		html = null;
		sb = null;
		return;
	}
	
	/**
	 * Show chat window3rd.
	 * @param player the player
	 */
	private void showChatWindow3rd(final L2PcInstance player)
	{
		NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
		TextBuilder sb = new TextBuilder();
		sb.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\">___________________________________________</font><img src=L2Submission.classmanager height=90 width=256><br1><font color=\"003366\" align=\"center\">___________________________________________</font></center><br1>");
		sb.append("<table width=200>");
		sb.append("<tr><td><center>GM Class Master:</center></td></tr>");
		sb.append("<tr><td><br></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 88\" value=\"Advance to " + CharTemplateTable.getClassNameById(88) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 89\" value=\"Advance to " + CharTemplateTable.getClassNameById(89) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 90\" value=\"Advance to " + CharTemplateTable.getClassNameById(90) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 91\" value=\"Advance to " + CharTemplateTable.getClassNameById(91) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 92\" value=\"Advance to " + CharTemplateTable.getClassNameById(92) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 93\" value=\"Advance to " + CharTemplateTable.getClassNameById(93) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 94\" value=\"Advance to " + CharTemplateTable.getClassNameById(94) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 95\" value=\"Advance to " + CharTemplateTable.getClassNameById(95) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 96\" value=\"Advance to " + CharTemplateTable.getClassNameById(96) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 97\" value=\"Advance to " + CharTemplateTable.getClassNameById(97) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 98\" value=\"Advance to " + CharTemplateTable.getClassNameById(98) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 99\" value=\"Advance to " + CharTemplateTable.getClassNameById(99) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 100\" value=\"Advance to " + CharTemplateTable.getClassNameById(100) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 101\" value=\"Advance to " + CharTemplateTable.getClassNameById(101) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 102\" value=\"Advance to " + CharTemplateTable.getClassNameById(102) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 103\" value=\"Advance to " + CharTemplateTable.getClassNameById(103) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 104\" value=\"Advance to " + CharTemplateTable.getClassNameById(104) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 105\" value=\"Advance to " + CharTemplateTable.getClassNameById(105) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 106\" value=\"Advance to " + CharTemplateTable.getClassNameById(106) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 107\" value=\"Advance to " + CharTemplateTable.getClassNameById(107) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 108\" value=\"Advance to " + CharTemplateTable.getClassNameById(108) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 109\" value=\"Advance to " + CharTemplateTable.getClassNameById(109) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 110\" value=\"Advance to " + CharTemplateTable.getClassNameById(110) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 111\" value=\"Advance to " + CharTemplateTable.getClassNameById(111) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 112\" value=\"Advance to " + CharTemplateTable.getClassNameById(112) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 113\" value=\"Advance to " + CharTemplateTable.getClassNameById(113) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 114\" value=\"Advance to " + CharTemplateTable.getClassNameById(114) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 115\" value=\"Advance to " + CharTemplateTable.getClassNameById(115) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 116\" value=\"Advance to " + CharTemplateTable.getClassNameById(116) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 117\" value=\"Advance to " + CharTemplateTable.getClassNameById(117) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 118\" value=\"Advance to " + CharTemplateTable.getClassNameById(118) + "\"></td></tr>");
		sb.append("</table>");
		sb.append("<font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");
		html.setHtml(sb.toString());
		player.sendPacket(html);
		html = null;
		sb = null;
		return;
	}
	
	/**
	 * Show chat window base.
	 * @param player the player
	 */
	private void showChatWindowBase(final L2PcInstance player)
	{
		NpcHtmlMessage html = new NpcHtmlMessage(getObjectId());
		TextBuilder sb = new TextBuilder();
		sb.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\">___________________________________________</font><img src=L2Submission.classmanager height=90 width=256><br1><font color=\"003366\" align=\"center\">___________________________________________</font></center><br1>");
		sb.append("<table width=200>");
		sb.append("<tr><td><center>GM Class Master:</center></td></tr>");
		sb.append("<tr><td><br></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 0\" value=\"Advance to " + CharTemplateTable.getClassNameById(0) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 10\" value=\"Advance to " + CharTemplateTable.getClassNameById(10) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 18\" value=\"Advance to " + CharTemplateTable.getClassNameById(18) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 25\" value=\"Advance to " + CharTemplateTable.getClassNameById(25) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 31\" value=\"Advance to " + CharTemplateTable.getClassNameById(31) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 38\" value=\"Advance to " + CharTemplateTable.getClassNameById(38) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 44\" value=\"Advance to " + CharTemplateTable.getClassNameById(44) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 49\" value=\"Advance to " + CharTemplateTable.getClassNameById(49) + "\"></td></tr>");
		sb.append("<tr><td><button width=256 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h npc_" + getObjectId() + "_change_class 53\" value=\"Advance to " + CharTemplateTable.getClassNameById(53) + "\"></td></tr>");
		sb.append("</table>");
		sb.append("<font color=\"003366\" align=\"center\">___________________________________________</font></center></body></html>");
		html.setHtml(sb.toString());
		player.sendPacket(html);
		html = null;
		sb = null;
		return;
	}
	
	/**
	 * Change class.
	 * @param player the player
	 * @param val the val
	 */
	private void changeClass(final L2PcInstance player, final int val)
	{
		if (Config.DEBUG)
		{
			LOGGER.info("Changing class to ClassId:" + val);
		}
		player.setClassId(val);
		
		if (player.isSubClassActive())
		{
			player.getSubClasses().get(player.getClassIndex()).setClassId(player.getActiveClass());
		}
		else
		{
			ClassId classId = ClassId.getClassIdByOrdinal(player.getActiveClass());
			
			if (classId.getParent() != null)
			{
				while (classId.level() == 0)
				{ // go to root
					classId = classId.getParent();
				}
			}
			
			player.setBaseClass(classId);
			
			// player.setBaseClass(player.getActiveClass());
		}
		
		player.broadcastUserInfo();
		player.broadcastClassIcon();
	}
}