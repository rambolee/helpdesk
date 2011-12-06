import logging
FORMAT = '%(asctime)s %(levelname)-8s %(message)s'

def log_control(logfile):
	logger = logging.getLogger()
	hdlr = logging.FileHandler(logfile)
	formatter = logging.Formatter(FORMAT)
	hdlr.setFormatter(formatter)
	logger.addHandler(hdlr)
	logger.setLevel(logging.INFO)
	return logger

if __name__ == "__main__":
	logfile = '/tmp/agent_11_05_23.log'
	logger = log_control(logfile)
	logger.error('abc')
	logger.info('test')
