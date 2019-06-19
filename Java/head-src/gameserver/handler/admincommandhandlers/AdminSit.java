/**
 * 
 */
package com.l2jfrozen.gameserver.handler.admincommandhandlers;

import java.util.Collection;
import java.util.StringTokenizer;

import com.l2jfrozen.gameserver.handler.IAdminCommandHandler;
import com.l2jfrozen.gameserver.model.L2Object;
import com.l2jfrozen.gameserver.model.L2Character;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.model.actor.instance.L2NpcInstance;
import com.l2jfrozen.gameserver.network.serverpackets.StopMove;

/**
 * @author Ventic
 *
 */
public class AdminSit implements IAdminCommandHandler
{
	private static final String[] ADMIN_COMMANDS = { "admin_sit" , "admin_stand", "admin_rangesit", "admin_rangestand" };

	@Override
	public boolean useAdminCommand(String command, L2PcInstance activeChar)
	{
		final StringTokenizer st = new StringTokenizer(command);
		st.nextToken();
		
		if (command.startsWith("admin_sit"))
		{
			L2Object target = activeChar.getTarget();
			if (target instanceof L2NpcInstance)
			{
			   activeChar.sendMessage("You can not use it at NPCs!");
               return false;
			}
            else if (target == null)
			{
				activeChar.sendMessage("You have no target!");
	            return false;
			}
			else
			{
				((L2PcInstance) target).sitDown();
			}
			
			String type = "1";
			try
			{
				type = st.nextToken();
			}
			catch (final Exception e)
			{
			}
			try
			{
				L2Character player = null;
				if (target instanceof L2Character)
				{
					player = (L2Character) target;
					if (type.equals("1"))
						player.startAbnormalEffect(0x0400);
					else
						player.startAbnormalEffect(0x0800);
					player.setIsParalyzed(true);
					final StopMove sm = new StopMove(player);
					player.sendPacket(sm);
					player.broadcastPacket(sm);
				}
			}
			catch (final Exception e)
			{
			}
		}
		
		if (command.startsWith("admin_stand"))
		{
			L2Object target = activeChar.getTarget();
			if (target instanceof L2NpcInstance)
			{
			   activeChar.sendMessage("You can not use it at NPCs!");
               return false;
			}
            else if (target == null)
			{
				activeChar.sendMessage("You have no target!");
	            return false;
			}
			else
			{
				((L2PcInstance) target).standUp();
			}
			
			try
			{
				L2Character player = null;
				if (target instanceof L2Character)
				{
					player = (L2Character) target;
					player.stopAbnormalEffect((short) 0x0400);
					player.setIsParalyzed(false);
				}
			}
			catch (final Exception e)
			{
			}
		}
		
		if (command.startsWith("admin_rangesit"))
		{
			Collection<L2Character> players = activeChar.getKnownList().getKnownCharactersInRadius(240);
			for (L2Character p : players)
			{
				if (p instanceof L2PcInstance)
				{
					((L2PcInstance) p).sitDown();
				}
			}
			
			try
			{
				for (final L2PcInstance player : activeChar.getKnownList().getKnownPlayers().values())
				{
					if (!player.isGM())
					{
						player.startAbnormalEffect(0x0400);
						player.setIsParalyzed(true);
						final StopMove sm = new StopMove(player);
						player.sendPacket(sm);
						player.broadcastPacket(sm);
					}
				}
			}
			catch (final Exception e)
			{
			}
		}
		
		if (command.startsWith("admin_rangestand"))
		{
			Collection <L2Character> players = activeChar.getKnownList().getKnownCharactersInRadius(240);
			for (L2Character p : players)
			{
				if (p instanceof L2PcInstance)
				{
					((L2PcInstance) p).standUp();
				}
			}
			
			try
			{
				for (final L2PcInstance player : activeChar.getKnownList().getKnownPlayers().values())
				{
					player.stopAbnormalEffect(0x0400);
					player.setIsParalyzed(false);
				}
			}
			catch (final Exception e)
			{
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