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


# set variables
now_dt = con._NOW_DT


# set class
sql = cMysql.cMysql(con.DB_INFO)
sql.db_conn()

cLog = cLogger.cLogger("api")
logger = cLog.set_logger()


try :
	sql.tran()
	
	# get html and bs4 parsing
	url = "http://www.amorepacificmall.com/main.do"

	req = requests.get(url)
	html = req.text
	soup = BeautifulSoup(html, 'html.parser')


	# 1차카테고리
	obj_cate = soup.find("li", {"class" : "tree_depth1"})

	list_cate = obj_cate.find("ul", {"class" : "tree_depth2"}).select("li > a")

	arr_cate1 = []
	for ds in list_cate :
		arr_cate2 = []

		depth = ds.get("data-depth")
		code = ds.get("data-category")
		name = ds.find("span").text
		arr_cate2.append(code)
		arr_cate2.append('')
		arr_cate2.append(depth)
		arr_cate2.append(name)
		arr_cate2.append(now_dt)

		arr_cate1.append(arr_cate2)
	#end for

	qry = """
		INSERT INTO TB_CATEGORY (CATE_CD, UPCATE_CD, DEPTH, CATE_NM, REG_DT)
		VALUES(%s, %s, %s, %s, %s)
	"""
	result = sql.execmany(qry, arr_cate1)


	# 2차카테고리
	list_cate = obj_cate.find_all("div", {"class" : "tree_depth3"})

	arr_cate1 = []
	for ds in list_cate :
		list_cate2 = ds.find_all("dt")
		
		for ds2 in list_cate2 :
			arr_cate2 = []

			obj_cate2 = ds2.find("a")
			arr_code = obj_cate2.get("data-category").split("^")

			code1 = arr_code[0]
			code2 = arr_code[1]
			depth = obj_cate2.get("data-depth")
			name = obj_cate2.text
			arr_cate2.append(code2)
			arr_cate2.append(code1)
			arr_cate2.append(depth)
			arr_cate2.append(name)
			arr_cate2.append(now_dt)

			arr_cate1.append(arr_cate2)
		#end for
	#end for

	qry = """
		INSERT INTO TB_CATEGORY (CATE_CD, UPCATE_CD, DEPTH, CATE_NM, REG_DT)
		VALUES(%s, %s, %s, %s, %s)
	"""
	result = sql.execmany(qry, arr_cate1)


	# 3차카테고리
	list_cate = obj_cate.find_all("div", {"class" : "tree_depth3"})

	arr_cate1 = []
	for ds in list_cate :
		list_cate2 = ds.find_all("dd")
		
		for ds2 in list_cate2 :
			arr_cate2 = []

			obj_cate2 = ds2.find("a")
			
			arr_code = obj_cate2.get("data-category").split("^")

			code1 = arr_code[0]
			code2 = arr_code[1]
			code3 = arr_code[2]
			depth = obj_cate2.get("data-depth")
			name = obj_cate2.text
			arr_cate2.append(code3)
			arr_cate2.append(code2)
			arr_cate2.append(depth)
			arr_cate2.append(name)
			arr_cate2.append(now_dt)

			arr_cate1.append(arr_cate2)
		#end for
	#end for

	qry = """
		INSERT INTO TB_CATEGORY (CATE_CD, UPCATE_CD, DEPTH, CATE_NM, REG_DT)
		VALUES(%s, %s, %s, %s, %s)
	"""
	result = sql.execmany(qry, arr_cate1)


	sql.commit()
#end try

except Exception as err:
	sql.rollback()
	logger.error("find error : "+ str(err))
#end except

finally :
	sql.close()
#end finally
