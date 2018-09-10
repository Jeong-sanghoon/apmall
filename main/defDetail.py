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
cLog = cLogger.cLogger("defDetail")
logger = cLog.set_logger()


# 상품코드 리스트 조회
def getProductCode(ctg1) :
	try :
		# set db
		sql = cMysql.cMysql(con.DB_INFO)
		sql.db_conn()

		qryWhere = ''
		if ctg1 != '' :
			qryWhere = " WHERE CATE_CD1 = %s"
			param.append(ctg1)
		# end if
		qryOrder = ' ORDER BY CATE_CD1, PRODUCT_SEQ'
		if ctg1 != '' :
			qryOrder = " ORDER BY PRODUCT_SEQ"
			param.append(ctg1)
		# end if

		param = []
		qry = """
			SELECT PRODUCT_CD 
			FROM TB_PRODUCT 
			"""+ qryWhere +"""
			"""+ qryOrder +"""
		"""
		logger.debug(qry)
		logger.debug(str(param))
		result = sql.exec('list', qry, param)
		cnt = result['cnt']
		rs = result['data']

		return rs
	#end try
	except Exception as err :
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def


# 상품데이터 조회
def getProductInfo(p_cd) :
	try :
		# set db
		sql = cMysql.cMysql(con.DB_INFO)
		sql.db_conn()

		param = []
		qry = """
			SELECT * FROM TB_PRODUCT WHERE PRODUCT_CD = %s
		"""
		param.append(p_cd)
		result = sql.exec('data', qry, param)
		cnt = result['cnt']
		ds = result['data']

		return ds
	#end try
	except Exception as err :
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def


# html요청 및 bs4 오브젝트 처리
def getHtmlObj(p_cd) :
	# get html and bs4 parsing
	url = "http://www.amorepacificmall.com/plist/11127/all/all/all/detail.do?i_sProductcd="+ p_cd

	req = requests.get(url)
	html = req.text
	soup = BeautifulSoup(html, 'html.parser')

	return soup
#end def


# 상품메인이미지 가져오기
def getMainImg(obj, dsPrd) :
	try :
		list_main_img = obj.find("ul", {"class" : "dR_img"}).find_all('li')

		arr_main_img = []
		for ds_img in list_main_img:
			isVod = fnc.IS_ELEMENT(ds_img.find('div', {'class' : 'video_inner'}))
			
			if isVod == False :
				img_url = ds_img.find('img').get('src')		# 이미지 url추출

				cnt = img_url.rfind("/") + 1				# 파일명 시작위치
				fullfilename = img_url[cnt:]				# 전체파일명
				filename = fullfilename.split(".")[0]		# 파일명
				ext = fullfilename.split(".")[1]			# 확장자

				#fnc.MAKE_FOLDER(con._UPLOAD_DIR +'/'+ dsPrd['v_brandcd'])

				cust_filename = dsPrd['BRAND_CD'] +"/"+ fullfilename
				urllib.request.urlretrieve(img_url, con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드

				arr_main_img.append(cust_filename)
			#end if
		#end for

		return arr_main_img
	#end try
	except Exception as err:
		logger.error("find error : "+ str(err))
	#end except
#end def


# 옵션정보 가져오기
def getOption(obj, dsPrd) :
	try :
		arr_option = []
		is_option = fnc.IS_ELEMENT(obj.find("div", {"id" : "listOptScroll1"}))

		if is_option == True :
			# 옵션이 있을때
			list_option = obj.find("div", {"id" : "listOptScroll1"}).find_all("dl", {"class" : "op_sell"})

			for obj_option in list_option :
				dic_option = {}
				optname = obj_option.find("dt").text.strip()
				optname = optname.replace("(일시품절)", "").strip()
				is_sale = fnc.IS_ELEMENT(obj_option.find("dd").find("del"))

				if is_sale == True :
					optprice = obj_option.find("dd").find("del").text.strip()
				else :
					optprice = obj_option.find("span", {"class" : "ff_rbt"}).text.strip()

				optprice = optprice.replace("원", "").replace(",", "")
				dic_option["title"] = optname
				dic_option["price"] = optprice

				arr_option.append(dic_option)
				#print(optname +" : "+ optprice)
		else :
			# 옵션이 없을때
			dic_option = {}
			
			opt = obj.select('.optionBoxList.1st')[0]
			optname = ''
			optprice = ''
			
			if fnc.IS_ELEMENT(opt.find('', {'class':'op_name'})) == True :
				optname = opt.select('.op_name')[0].text.strip()
				optname = optname.replace("[일시품절]", "").strip()
			else :
				optname = dsPrd['PRODUCT_NM']
			# end if
			if fnc.IS_ELEMENT(opt.find('', {'class':'op_pay'})) == True :
				optprice = opt.select('.op_pay')[0].text.strip()
				optprice = optprice.replace("원", "").replace(",", "")
			else :
				optprice = dsPrd['PRICE']
			# end if
			
			dic_option["title"] = optname
			dic_option["price"] = optprice
			arr_option.append(dic_option)
		#end if
		
		return arr_option
	#end try
	except Exception as err:
		logger.error("find error : "+ str(err))
	#end except
#end def


# 상품상세이미지 가져오기
def getDetailImg(obj, dsPrd) :
	try :
		list_desc_img = obj.find("", {"id" : "reN_infoP"}).find_all('img')

		arr_desc_img = []
		for ds_img in list_desc_img :
			img_url = ds_img.get("src")

			cnt = img_url.rfind("/") + 1		# 파일명 시작위치
			fullfilename = img_url[cnt:]		# 전체파일명
			filename = fullfilename.split(".")[0]		# 파일명
			ext = fullfilename.split(".")[1]			# 확장자

			# fnc.MAKE_FOLDER(con._UPLOAD_DIR +'/'+ dsPrd['v_brandcd'])

			cust_filename = dsPrd['BRAND_CD'] +"/"+ fullfilename
			urllib.request.urlretrieve(img_url, con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드
			
			arr_desc_img.append(cust_filename)
		#end for

		return arr_desc_img
	#end try
	except Exception as err :
		logger.error("find error : "+ str(err))

		arr_desc_img = []
		return arr_desc_img
	#end except
#end def


# db transaction
def setDetailInfo(arrMain) :
	try :
		# set db
		sql = cMysql.cMysql(con.DB_INFO)
		sql.db_conn()
		sql.tran()

		# set variables
		product_seq = arrMain['product_seq']
		product_nm2 = arrMain['product_nm2']


		# detail product name update
		param = []
		qry = """
			UPDATE TB_PRODUCT SET
			PRODUCT_NM2 = %s
			WHERE PRODUCT_SEQ = %s
		"""
		param.append(product_nm2)
		param.append(product_seq)
		logger.debug(qry)
		logger.debug(str(param))
		result = sql.exec('update', qry, param)
		cnt = result['cnt']

		if cnt < 1 :
			raise Exception("상세상품명 업데이트 카운트 오류")
		#end if


		# main image insert
		list_main_img = arrMain["main_img"]
		param = []
		
		for main_img in list_main_img :
			arr = []
			arr.append(product_seq)
			arr.append(main_img)
			arr.append('M')
			arr.append(now_dt)
			param.append(arr)
		#end for

		qry = """
			INSERT INTO TB_IMG (PRODUCT_SEQ, FILE_NM, IMG_TP, REG_DT)
			VALUES(%s, %s, %s, %s)
		"""
		logger.debug(qry)
		logger.debug(str(param))
		result = sql.execmany(qry, param)
		cnt = result['cnt']

		if cnt < 1 :
			raise Exception("메인이미지 등록 카운트 오류")
		#end if


		# detail image insert
		list_desc_img = arrMain['desc_img']

		if len(list_desc_img) > 0 :
			param = []

			for desc_img in list_desc_img :
				arr = []
				arr.append(product_seq)
				arr.append(desc_img)
				arr.append('D')
				arr.append(now_dt)
				param.append(arr)
			#end for

			qry = """
				INSERT INTO TB_IMG (PRODUCT_SEQ, FILE_NM, IMG_TP, REG_DT)
				VALUES(%s, %s, %s, %s)
			"""
			logger.debug(qry)
			logger.debug(str(param))
			result = sql.execmany(qry, param)
			cnt = result['cnt']

			if cnt < 1 :
				raise Exception("상세이미지 등록 카운트 오류")
			#end if
		#end if

		# option insert
		list_option = arrMain["option"]

		if list_option != None :
			param = []
			
			for option in list_option :
				arr = []
				arr.append(product_seq)
				arr.append(option['title'])
				arr.append(option['price'])
				arr.append(now_dt)
				param.append(arr)
			#end for

			qry = """
				INSERT INTO TB_OPTION (PRODUCT_SEQ, OPT_NM, OPT_PRICE, REG_DT)
				VALUES(%s, %s, %s, %s)
			"""
			logger.debug(qry)
			logger.debug(str(param))
			result = sql.execmany(qry, param)
			cnt = result['cnt']

			if cnt < 1 :
				raise Exception("메인이미지 등록 카운트 오류")
			#end if
		#end if
		
		sql.commit()
	#end try
	except Exception as err :
		sql.rollback()
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def