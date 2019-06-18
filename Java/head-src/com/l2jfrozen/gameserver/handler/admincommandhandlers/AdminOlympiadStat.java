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
 * [url]http://www.gnu.org/copyleft/gpl.html[/url]
 */
package com.l2jfrozen.gameserver.handler.admincommandhandlers;

import java.util.logging.Logger;

import com.l2jfrozen.gameserver.handler.IAdminCommandHandler;
import com.l2jfrozen.gameserver.model.L2Object;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.model.entity.olympiad.Olympiad;

public class AdminOlympiadStat implements IAdminCommandHandler
{
	protected static final Logger _log = Logger.getLogger(AdminOlympiadStat.class.getName());

	private static String[] ADMIN_COMMANDS = { "admin_olympiad_stat" };

	@Override
	public boolean useAdminCommand(String command, L2PcInstance activeChar)
	{
		if(command.startsWith("admin_olympiad_stat"))
		{
			L2Object target = activeChar.getTarget();
			L2PcInstance player = null;
			
			if(target != null && target instanceof L2PcInstance)
				player = (L2PcInstance)target;
			else
				activeChar.sendMessage("Usage: //olympiad_stat <target>");
			
			if(player.isNoble())
			{
				activeChar.sendMessage("Match(s): " + Olympiad.getInstance().getCompetitionDone(player.getObjectId()));
				activeChar.sendMessage("Points: "+Olympiad.getInstance().getNoblePoints(player.getObjectId()));
				return true;
			}
			else
			{
				activeChar.sendMessage("Oops! Your target is not a Noble!");
				return true;
			}
		}
		return true;
	}

	@Override
	public String[] getAdminCommandList()
	{
		return ADMIN_COMMANDS;
	}
}