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


#set variables
now_dt = con._NOW_DT


# set class
cLog = cLogger.cLogger("defApi")
logger = cLog.set_logger()


def main() :
	global logger
	global now_dt

	sql = cMysql.cMysql(con.DB_INFO)
	sql.db_conn()

	try :
		# get 1depth Category list
		param = []
		qry = """
			SELECT CATE_CD, CATE_NM
			FROM TB_CATEGORY
			WHERE USE_YN = 'Y'
			AND DEPTH = 1
			ORDER BY CATE_CD
		"""
		result = sql.exec('list', qry, param)
		cnt = result['cnt']
		rs = result['data']

		sql.tran()

		dicCate = fnc.GET_CATEGORY_DICT()

		for ds in rs :
			cate_cd = ds['CATE_CD']
			cate_nm = ds['CATE_NM']
			totalPageCnt = 0

			url = "http://www.amorepacificmall.com/plist/all/"+ cate_cd +"/all/all/list_paging_ajax.do"
			headers = {'Content-Type': 'application/json; charset=utf-8'}
			param = {"i_iNowPageNo": 1, "i_sCategorycd1" : cate_cd}
			res = requests.post(url, headers = headers, data=json.dumps(param))
			
			if res.status_code != 200 :
				raise Exception("URL호출에러(1)")
			else :
				jsonData = res.json()
				dsPage = jsonData['shopProd']['ctgProdPage']

				totalPageCnt = dsPage['i_iTotalPageCnt']

				# api loop
				logger.info(cate_nm +" : "+ totalPageCnt)
				count = run_api(cate_cd, cate_nm, totalPageCnt, dicCate, sql)
				logger.info("success count : "+ str(count))
			#end if
		#end for

		sql.commit()
	#end try
	except Exception as err:
		sql.rollback()
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def


# call api for get data
def run_api(cate_cd, cate_nm, totalPageCnt, dicCate, sql) :
	global logger
	global now_dt

	arr_main = []

	nCnt = 0
	for i in range(1, (int(totalPageCnt) + 1), 1) :
		url = "http://www.amorepacificmall.com/plist/all/"+ cate_cd +"/all/all/list_paging_ajax.do"
		headers = {'Content-Type': 'application/json; charset=utf-8'}
		param = {"i_iNowPageNo": i, "i_sCategorycd1" : cate_cd}
		res = requests.get(url, headers = headers, params=param)
		
		if res.status_code != 200 :
			raise Exception("URL호출에러(1)")
		else :
			jsonData = res.json()
			rsData = jsonData['shopProd']['ctgProds']

			logger.info(str(i) +" START LOOP")
			for data in rsData :
				# 기존에 존재하는 상품인지 확인 (부하정도 확인후 활성화)
				#isPass = fnc.IS_PRODUCT(data['v_productcd'])
				isPass = 0

				if isPass < 1 :
					# 카테고리명
					cate_cd1 = ''
					cate_cd2 = ''
					cate_cd3 = ''
					arr_cate = data['v_prod_ctg_path'].split(">")
					if len(arr_cate) == 3 :
						key1 = arr_cate[0] +'_1'
						key2 = arr_cate[1] +'_2'
						key3 = arr_cate[2] +'_3'
						
						cate_cd1 = dicCate[key1]
						cate_cd2 = dicCate[key2]
						cate_cd3 = dicCate[key3]
					elif len(arr_cate) == 2 :
						key1 = arr_cate[0] +'_1'
						key2 = arr_cate[1] +'_2'

						cate_cd1 = dicCate[key1]

						if arr_cate[1] != '베이비' :
							cate_cd2 = dicCate[key2]
						# end if
					elif len(arr_cate) == 1 :
						key1 = arr_cate[0] +'_1'
						
						cate_cd1 = dicCate[key1]
					# end if

					# 썸네일
					img_path = ''
					free_img_path = ''

					fnc.MAKE_FOLDER(con._UPLOAD_DIR +'/'+ data['v_brandcd'])

					if 'v_img_path' in data.keys() :
						cnt = data['v_img_path'].rfind("/") + 1		# 파일명 시작위치
						fullfilename = data['v_img_path'][cnt:]		# 전체파일명
						filename = fullfilename.split(".")[0]		# 파일명
						ext = fullfilename.split(".")[1]			# 확장자

						#cust_filename = filename +"_"+ str(_NOW_DT.microsecond) +"."+ ext
						cust_filename = data['v_brandcd'] +'/'+ fullfilename
						urllib.request.urlretrieve(data['v_img_path'], con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드

						img_path = cust_filename

					if 'v_free_img_path' in data.keys() :
						cnt = data['v_free_img_path'].rfind("/") + 1		# 파일명 시작위치
						fullfilename = data['v_free_img_path'][cnt:]		# 전체파일명
						filename = fullfilename.split(".")[0]				# 파일명
						ext = fullfilename.split(".")[1]					# 확장자

						#cust_filename = filename +"_"+ str(_NOW_DT.microsecond) +"."+ ext
						cust_filename = data['v_brandcd'] +'/'+ fullfilename
						urllib.request.urlretrieve(data['v_free_img_path'], con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드

						free_img_path = cust_filename

					# 상품설명
					v_comment = ''
					if 'v_comment' in data.keys() :
						v_comment = data['v_comment']
					else :
						v_comment = data['v_nickname']

					arr_data = []
					arr_data.append(data['v_productcd'])
					arr_data.append(data['v_productnm'])
					arr_data.append(v_comment)
					arr_data.append(data['v_brandcd'])
					arr_data.append(data['v_brandnm'])
					arr_data.append(data['n_list_price'])
					arr_data.append(cate_cd1)
					arr_data.append(cate_cd2)
					arr_data.append(cate_cd3)
					arr_data.append(img_path)
					arr_data.append(free_img_path)
					arr_data.append(now_dt)

					arr_main.append(arr_data)
				else :
					logger.info("already insert product : "+ data['v_productcd'])
				#end if
			#end for
			logger.info(str(i) +" END LOOP")
		#end if
	#end for
	
	#start database
	if len(arr_main) > 0 :
		qry = """
			INSERT INTO TB_PRODUCT (PRODUCT_CD, PRODUCT_NM, PRODUCT_DESC, BRAND_CD, BRAND_NM, PRICE, CATE_CD1, CATE_CD2, CATE_CD3, THUMB1, THUMB2, REG_DT)
			VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
		"""

		result = sql.execmany(qry, arr_main)
		nCnt = nCnt + int(result['cnt'])
	#end if

	return nCnt
#end def


def deleteSameData() :
	global logger

	sql = cMysql.cMysql(con.DB_INFO)
	sql.db_conn()

	try :
		param = []
		qry = """
			DELETE FROM TB_PRODUCT WHERE PRODUCT_SEQ IN (
				SELECT PRODUCT_SEQ FROM (
					SELECT MAX(PRODUCT_SEQ) AS PRODUCT_SEQ, PRODUCT_CD, COUNT(*) AS CNT FROM `TB_PRODUCT` GROUP BY `PRODUCT_CD`
				) A
				WHERE A.CNT > 1
			)
		"""
		logger.debug(qry)
		result = sql.exec('update', qry, param)
		logger.info("delete count == "+ str(result['cnt']))
	#end try
	except Exception as err:
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def