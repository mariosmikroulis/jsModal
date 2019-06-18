package com.l2jfrozen.gameserver.handler.voicedcommandhandlers;

import com.l2jfrozen.gameserver.handler.IVoicedCommandHandler;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.network.serverpackets.NpcHtmlMessage;

public class ServerInfo implements IVoicedCommandHandler
{
       private static final String[] VOICED_COMMANDS ={ "serverinfo_main", "serverinfo_generalinfo", "serverinfo_voicecommands", "serverinfo_contactinfo", "serverinfo_npcinfo", "serverinfo_farminfo", "serverinfo_raidinfo", "serverinfo_finalinfo"};
      
       @Override
       public boolean useVoicedCommand(String command, L2PcInstance activeChar, String target)
       {
    	   if (command.startsWith("serverinfo_main"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   
    	   else if (command.startsWith("serverinfo_generalinfo"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666-1.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   
    	   else if (command.startsWith("serverinfo_eventinfo"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666-2.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   
    	   else if (command.startsWith("serverinfo_voicecommands"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666-3.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   
    	   else if (command.startsWith("serverinfo_contactinfo"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666-4.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   
    	   else if (command.startsWith("serverinfo_npcinfo"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666-5.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   
    	   else if (command.startsWith("serverinfo_farminfo"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666-6.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   
    	   else if (command.startsWith("serverinfo_raidinfo"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666-7.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   else if (command.startsWith("serverinfo_finalinfo"))
    	   {
    		   NpcHtmlMessage html = new NpcHtmlMessage(1);
    		   html.setFile("data/html/merchant/6666-8.htm");
    		   html.replace("%playername%", activeChar.getName());
    		   activeChar.sendPacket(html);
    	   }
    	   
    	   return true;
       }
       
       @Override
       public String[] getVoicedCommandList()
       {
               return VOICED_COMMANDS;
       }
}