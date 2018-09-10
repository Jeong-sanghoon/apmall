# import library
import logging
import logging.handlers


# logging class
# level : DEBUG, INFO, WARNING, ERROR, CRITICAL
class cLogger :
	def __init__(self, name) :
		self.logger = logging.getLogger(name)
		self.logger.setLevel(logging.DEBUG)


	def set_logger(self) :
		formatter = logging.Formatter("[%(asctime)s] [%(name)s] [%(levelname)s] [%(filename)s:%(lineno)d] => %(message)s")

		sh = logging.StreamHandler()
		sh.setLevel(logging.DEBUG)
		sh.setFormatter(formatter)
		self.logger.addHandler(sh)

		filename = "/home/crawler/_logger/crawler.log"
		fileMaxByte = 1024 * 1024 * 1000	#1.0GB
		fh = logging.handlers.RotatingFileHandler(filename, maxBytes=fileMaxByte, backupCount=10)
		#fh = logging.FileHandler(filename)
		fh.setLevel(logging.DEBUG)
		fh.setFormatter(formatter)
		self.logger.addHandler(fh)

		return self.logger