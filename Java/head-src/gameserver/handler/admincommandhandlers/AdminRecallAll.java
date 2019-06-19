/* This program is free software; you can redistribute it and/or modify
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
package com.l2jfrozen.gameserver.handler.admincommandhandlers;

import com.l2jfrozen.Config;
import com.l2jfrozen.gameserver.handler.IAdminCommandHandler;
import com.l2jfrozen.gameserver.managers.GrandBossManager;
import com.l2jfrozen.gameserver.model.L2Character;
import com.l2jfrozen.gameserver.model.L2World;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.network.SystemMessageId;
import com.l2jfrozen.gameserver.network.serverpackets.ConfirmDlg;
import com.l2jfrozen.gameserver.thread.ThreadPoolManager;
import com.l2jfrozen.gameserver.util.Util;



public class AdminRecallAll implements IAdminCommandHandler
{
   private static final String[] ADMIN_COMMANDS = { "admin_recallall" };
   public static boolean isAdminSummoning = false;
   public static int x = 0;
   public static int y = 0;
   public static int z = 0;

   
   public boolean useAdminCommand(String command, L2PcInstance activeChar)
    {
        if (command.startsWith("admin_recallall"))
        {
           x = activeChar.getX();
           y = activeChar.getY();
           z = activeChar.getZ();
           isAdminSummoning = true;
           
           
              for(L2PcInstance player : L2World.getInstance().getAllPlayers())
              {                         
              try
              {
                     if (!L2PcInstance.checkSummonTargetStatus(player, activeChar)
                         ||  player.isAlikeDead()
                        ||  player._inEvent
                        ||  player._inEventCTF
                        ||  player._inEventDM
                        ||  player._inEventTvT
                        ||  player._inEventVIP
                        ||  player.isInStoreMode()
                        ||   player.isRooted() || player.isInCombat()
                        ||   (GrandBossManager.getInstance().getZone(player) != null && !player.isGM())
                        ||   player.isInOlympiadMode()
                        ||   player.isFestivalParticipant()
                        ||   player.isInsideZone(L2Character.ZONE_PVP)
                         )
                        continue;

                     if(!Util.checkIfInRange(0, activeChar, player, false))
                     {   
                           ThreadPoolManager.getInstance().scheduleGeneral(new Restore(), 15000);                     
                        ConfirmDlg confirm = new ConfirmDlg(SystemMessageId.S1_WISHES_TO_SUMMON_YOU_FROM_S2_DO_YOU_ACCEPT.getId());
                     confirm.addString(activeChar.getName());
                     confirm.addZoneName(activeChar.getX(), activeChar.getY(), activeChar.getZ());
                     confirm.addTime(15000);
                     confirm.addRequesterId(activeChar.getObjectId());
                     player.sendPacket(confirm);
                     }                  
                     player = null;           
               }
               catch(Throwable e)
               {
                  if(Config.ENABLE_ALL_EXCEPTIONS)
                     e.printStackTrace();
               }
              }     
             
        }
        return false;
       
    }
   
    class Restore implements Runnable
    {
      public void run()
      {
         x = 0;
           y = 0;
           z = 0;
           isAdminSummoning = false;                     
      }
       
    }
    public String[] getAdminCommandList()
    {
        return ADMIN_COMMANDS;
    }
}