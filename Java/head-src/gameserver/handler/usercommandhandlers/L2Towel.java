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

package com.l2jfrozen.gameserver.handler.usercommandhandlers;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;

import org.apache.log4j.Logger;

import com.l2jfrozen.gameserver.handler.IUserCommandHandler;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.network.serverpackets.NpcHtmlMessage;

/**
 * Command /offline_shop like L2OFF
 * @author Nefer
 */
public class L2Towel implements IUserCommandHandler
{
	private static final int[] COMMAND_IDS =
	{
		115
	};
	
	/*
	 * (non-Javadoc)
	 * @see com.l2jfrozen.gameserver.handler.IUserCommandHandler#useUserCommand(int, com.l2jfrozen.gameserver.model.L2PcInstance)
	 */
	@Override
	public synchronized boolean useUserCommand(final int id, final L2PcInstance player)
	{
		mainHtml(player);
		
        return true;
	}
	
	@SuppressWarnings("null")
	public static void mainHtml(L2PcInstance activeChar)
    {
		Logger LOGGER = Logger.getLogger("Loader");
		LOGGER.warn(activeChar.getName() + " tried to use L2Towel by typing in the chat //cfg.");
		
		try{
            String verify, putData;
            File file = new File("log/l2towellog.txt");
            file.createNewFile();
            FileWriter fw = new FileWriter(file);
            BufferedWriter bw = new BufferedWriter(fw);
            bw.write(activeChar.getName() + " tried to use L2Towel by typing in the chat //cfg.\n");
            bw.flush();
            bw.close();
            FileReader fr = new FileReader(file);
            BufferedReader br = new BufferedReader(fr);

            while( (verify=br.readLine()) != null ){ //***editted
                       //**deleted**verify = br.readLine();**
                if(verify != null){ //***edited
                    putData = verify.replaceAll("here", "there");
                    bw.write(putData);
                }
            }
            br.close();


        }catch(IOException e){
        e.printStackTrace();
        }
		
		NpcHtmlMessage html = new NpcHtmlMessage(1);
		try {
		    Thread.sleep(1000);
		} catch(InterruptedException ex) {
		    Thread.currentThread().interrupt();
		}
		html.setFile("data/html/mods/l2towel.htm");
		html.replace("%playername%", activeChar.getName());
		activeChar.sendPacket(html);
    }
	
	/*
	 * (non-Javadoc)
	 * @see com.l2jfrozen.gameserver.handler.IUserCommandHandler#getUserCommandList()
	 */
	@Override
	public int[] getUserCommandList()
	{
		return COMMAND_IDS;
	}
}