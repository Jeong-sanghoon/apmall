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
sql = cMysql.cMysql(con.DB_INFO)
sql.db_conn()

cLog = cLogger.cLogger("t_api")
logger = cLog.set_logger()


# call api for get data
def run_api(cate_cd, cate_nm, totalPageCnt) :
	global logger, sql
	global now_dt

	dicCate = fnc.GET_CATEGORY_DICT()
	
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

			for data in rsData :
				# 카테고리명
				cate_nm1 = ''
				cate_nm2 = ''
				cate_nm3 = ''
				arr_cate = data['v_prod_ctg_path'].split(">")
				if len(arr_cate) == 3 :
					key1 = arr_cate[0] +'_1'
					key2 = arr_cate[1] +'_2'
					key3 = arr_cate[2] +'_3'
					
					cate_nm1 = dicCate[key1]
					cate_nm2 = dicCate[key2]
					cate_nm3 = dicCate[key3]
				if len(arr_cate) == 2 :
					key1 = arr_cate[0] +'_1'
					key2 = arr_cate[1] +'_2'

					cate_nm1 = dicCate[key1]
					cate_nm2 = dicCate[key2]
				if len(arr_cate) == 1 :
					key1 = arr_cate[0] +'_1'
					
					cate_nm1 = dicCate[key1]

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
				arr_data.append(cate_nm1)
				arr_data.append(cate_nm2)
				arr_data.append(cate_nm3)
				arr_data.append(img_path)
				arr_data.append(free_img_path)
				arr_data.append(now_dt)

				arr_main.append(arr_data)
			#end for
		#end if
	#end for
	
	#start database
	if len(arr_main) > 0 :
		qry = """
			INSERT INTO TMP_PRODUCT (PRODUCT_CD, PRODUCT_NM, PRODUCT_DESC, BRAND_CD, BRAND_NM, PRICE, CATE_CD1, CATE_CD2, CATE_CD3, THUMB1, THUMB2, REG_DT)
			VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
		"""

		result = sql.execmany(qry, arr_main)
		nCnt = nCnt + int(result['cnt'])
	#end if

	return nCnt
#end def


try :
	cate_cd = 'CTG004'
	cate_nm = ''
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

		logger.info(cate_nm +" : "+ totalPageCnt)
		count = run_api(cate_cd, cate_nm, totalPageCnt)
		logger.info("success count : "+ str(count))
		#logger.info("success count : "+ count)
	#end if
#end try

except Exception as err:
	sql.rollback()
	logger.error("find error : ". str(err))
#end except

finally :
	sql.close()
#end finally
