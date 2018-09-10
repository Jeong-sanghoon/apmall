#import system library
import sys
import requests
import urllib.request			#이미지 다운로드
import json
from datetime import datetime
from bs4 import BeautifulSoup	#크롤링

# import user library
sys.path.insert(0, '/home/crawler')
from _lib import config as con
from _lib import cMysql
from _lib import cLogger
from _lib import function as fnc

# import def
import defApi as api


# set class
cLog = cLogger.cLogger("getApi")
logger = cLog.set_logger()


try :
	# run main function
	api.main()

	# delete same data
	api.deleteSameData()
#end try
finally :
	logger.info("api run complete!!")
#end finally
