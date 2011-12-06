import ConfigParser
import sys,os,string
import time

class MailConf:
	def __init__(self, logger):
		self.logger = logger
		self.cf = ''

	def read_conf(self):
		try:
			cf = ConfigParser.ConfigParser()
			conf_file = sys.path[0] + '/mail_config.conf'
			#print conf_file
			cf.read(conf_file)
			self.cf = cf
			self.conf_file = conf_file
		except:
			self.logger.error('can not read collector.conf file')

	def get_mail_account(self):	
		account_info = {}
		account_info['pop3_addr'] = self.cf.get('mail_account','pop3_addr') 
		account_info['pop3_user'] = self.cf.get('mail_account','pop3_user') 
		account_info['pop3_pass'] = self.cf.get('mail_account','pop3_pass') 
		return account_info 
	
	def get_mail_dir_info(self):
		mail_dir_info = {}
		mail_dir_info['path'] = self.cf.get('mail_dir','path') 
		mail_dir_info['incoming_dir_name'] = self.cf.get('mail_dir','incoming_dir_name') 
		return mail_dir_info

	def get_mail_cursor(self):
		return self.cf.getint('mail_cursor','cursor') 
	
	def set_mail_cursor(self,cursor_now):
		self.cf.set('mail_cursor','cursor',cursor_now)
		with open(self.conf_file, 'wb') as config_file: 
			self.cf.write(config_file)
	
	def get_max_query_count(self):
		return self.cf.getint('mail_cursor','max_query_count') 

