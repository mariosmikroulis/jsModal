/*
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
package com.l2jfrozen.gameserver.handler.itemhandlers;

/**
 * 
 * 
 * @author Coyote
 * Adapted by Strike
 */


import com.l2jfrozen.Config;
import com.l2jfrozen.gameserver.handler.IItemHandler;
import com.l2jfrozen.gameserver.model.actor.instance.L2ItemInstance;
import com.l2jfrozen.gameserver.model.actor.instance.L2PlayableInstance;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.network.serverpackets.MagicSkillUser;

public class ClanRepsItem implements IItemHandler
{
    private static final int ITEM_IDS[] =
    {
       Config.CR_ITEM_REPS_ITEM_ID
    };

    public void useItem(L2PlayableInstance playable, L2ItemInstance item)
    {
            if (!(playable instanceof L2PcInstance))
            {
                return;
            }

            L2PcInstance activeChar = (L2PcInstance)playable;

            if (!activeChar.isClanLeader())
            {
                activeChar.sendMessage("This can be used only by Clan Leaders!");
                return;
            }
           
            else if (!(activeChar.getClan().getLevel() >= Config.CR_ITEM_MIN_CLAN_LVL))
            {
               activeChar.sendMessage("Your Clan Level is not big enough to use this item!");
               return;
            }
            else
            {
               activeChar.getClan().setReputationScore(activeChar.getClan().getReputationScore()+Config.CR_ITEM_REPS_TO_BE_AWARDED, true);
               activeChar.sendMessage("Your clan has earned "+ Config.CR_ITEM_REPS_TO_BE_AWARDED +" rep points!");
               MagicSkillUser  MSU = new MagicSkillUser(activeChar, activeChar, 2024, 1, 1, 0);
               activeChar.broadcastPacket(MSU);
              playable.destroyItem("Consume", item.getObjectId(), 1, null, false);
            }
        }

    public int[] getItemIds()
    {
        return ITEM_IDS;
    }
}