/*
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */
package com.l2jfrozen.gameserver.handler.voicedcommandhandlers;

import com.l2jfrozen.Config;
import com.l2jfrozen.gameserver.handler.IVoicedCommandHandler;
import com.l2jfrozen.gameserver.model.L2Effect;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.model.PcInventory;
import com.l2jfrozen.gameserver.model.Inventory;
import com.l2jfrozen.gameserver.network.serverpackets.MagicSkillUser;
import com.l2jfrozen.gameserver.datatables.SkillTable;


/**
 * @author Elfocrash
 */

public class VoteRewardMe implements IVoicedCommandHandler
{
	private static final String[] VOICED_COMMANDS =
	{
			"rewardme"
	};

	
	@Override
	public boolean useVoicedCommand(String command, L2PcInstance activeChar, String target)
	{

			if(activeChar.isInOlympiadMode() || activeChar.getOlympiadGameId() != -1)
			{
				activeChar.sendMessage("You can't use that command inside Olympiad");
				return false;
			}

			if(activeChar.getInventory().getItemByItemId(Config.VOTE_ITEM_ID).getCount() >= Config.VOTE_ITEM_AMOUNT)
			{
				activeChar.getInventory().destroyItemByItemId("Consume", Config.VOTE_ITEM_ID, Config.VOTE_ITEM_AMOUNT, activeChar, null);
				activeChar.sendMessage(Config.VOTE_ITEM_AMOUNT + " " + Config.VOTE_ITEM_NAME + "(s) have been consumed.");
				MagicSkillUser mgc = new MagicSkillUser(activeChar, activeChar, Config.VOTE_BUFF_ID, Config.VOTE_BUFF_LVL, 1, 0);
				SkillTable.getInstance().getInfo(Config.VOTE_BUFF_ID, Config.VOTE_BUFF_LVL).getEffects(activeChar,activeChar);
				activeChar.broadcastPacket(mgc);
				activeChar.sendMessage("You have been blessed with the effects of the Vote Buff!");	
			}
			else
			{			
				activeChar.sendMessage("You don't have enough " + Config.VOTE_ITEM_NAME + "(s) in order to get rewarded!");
			}
			return true;	
	}

	@Override
	public String[] getVoicedCommandList()
	{
		return VOICED_COMMANDS;
	}
}