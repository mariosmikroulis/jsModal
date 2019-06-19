package com.l2jfrozen.gameserver.handler.admincommandhandlers;

import com.l2jfrozen.Config;
import com.l2jfrozen.gameserver.handler.IAdminCommandHandler;
import com.l2jfrozen.gameserver.model.L2Object;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;

/**
 * 
 * @author Elfocrash
 */
public class AdminDeport implements IAdminCommandHandler
{
	private static String[] _adminCommands =
	{
			"admin_deport"
	};

	@Override
	public boolean useAdminCommand(String command, L2PcInstance activeChar)
	{

		L2Object target = activeChar.getTarget();

		if(activeChar.getTarget() instanceof L2PcInstance)
		{
			if(command.startsWith("admin_deport"))
			{
				((L2PcInstance) activeChar.getTarget()).teleToLocation(Config.DEPORT_X, Config.DEPORT_Y, Config.DEPORT_Z);
			}
		}
		return false;

	}

	@Override
	public String[] getAdminCommandList()
	{
		return _adminCommands;
	}
}