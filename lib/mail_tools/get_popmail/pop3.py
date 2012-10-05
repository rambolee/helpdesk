#!/home/libo03/.jumbo/bin/python
# -*- coding:utf-8 -*-

# pop3.py  
import models.config as conf
import models.logger as log
import sys,os,string,binascii
import time
import poplib  
  
class mail_client:
	def __init__(self, mail_conf, logger):
		self.logger = logger 
		self.mail_conf = mail_conf
		
		mail_account = self.mail_conf.get_mail_account()
		self.pp = poplib.POP3_SSL(mail_account['pop3_addr'])  
		self.pp.user(mail_account['pop3_user'])  
		self.pp.pass_(mail_account['pop3_pass'])  

	def __del__(self):
		self.pp.quit()
	
	def getStat(self):
		stat = self.pp.stat()
		self.logger.info("stat current mail account is %s" %str(stat) )
		return stat

	def setCursor(self, cursor):
		self.mail_conf.set_mail_cursor(cursor)
		self.logger.info("set current mail cursor is %d" %(cursor)) 

	def resetCursor(self):
		self.mail_conf.set_mail_cursor(0)
		self.logger.warning("RESET current mail cursor to 0") 

	def getMails(self):
		cursor = self.mail_conf.get_mail_cursor()
		max_query_count = self.mail_conf.get_max_query_count()
		stat = self.getStat()
		total_count = int(stat[0])

		mail_dir_info = self.mail_conf.get_mail_dir_info()
		mail_dir_path = mail_dir_info['path'] + '/' + mail_dir_info['incoming_dir_name'] + '/'

		i = 0;
		if (total_count - cursor) >= max_query_count :
			for i in range(cursor, max_query_count + cursor):
				self.__getMail(i, mail_dir_path)
				new_cursor = i + 1
				self.mail_conf.set_mail_cursor( new_cursor )
		elif total_count > cursor :
			for i in range(cursor, total_count):
				self.__getMail(i, mail_dir_path)
				new_cursor = i + 1
				self.mail_conf.set_mail_cursor( new_cursor )



	def __getMail(self, current_cursor, mail_dir_path):
		mail_content = ''
		mail_data = self.pp.retr(current_cursor)
		for line in mail_data[1]:
			mail_content += line + "\n"
		self.__saveToFile(current_cursor, mail_content, mail_dir_path)

	def __saveToFile(self, index, mail_content, mail_dir_path):
		file_name = str(int(time.time() * 100)) + '_' + str(index)
		file_full_name = mail_dir_path + file_name
		f = file(file_full_name, 'w')
		f.write(mail_content)
		f.close()


if __name__ == '__main__':
	logger = log.log_control('/tmp/mail_agent.log') 
	mail_conf = conf.MailConf(logger) 
	mail_conf.read_conf()

	r = mail_client(mail_conf, logger)
	r.getMails()
