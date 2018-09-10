#import system library
import os, sys
from datetime import datetime
from PIL import Image			# 이미지처리

# import user library
sys.path.insert(0, '/home/crawler')
from _lib import config as con
from _lib import cMysql
from _lib import function as fnc

#####################################################################################
### common function
#####################################################################################
# 폴더생성
# description : 해당 경로에 폴더가 없으면 생성
# param
# 	location - 생성할 폴더 경로
def MAKE_FOLDER(location) :
	if not os.path.isdir(location) :
		os.mkdir(location)
	#end if
#end def


# 썸네일만들기
# param
# 	src - 원본이미지
# 	savefile - 저장될 파일명(경로포함)
# 	arr_size - 가로/세로 사이즈(리스트형식)
def MAKE_THUMB(src, savefile, arr_size) :
	im = Image.open(src)
	size = (arr_size[0], arr_size[1])
	im.thumbnail(size)
	im.save(savefile)
#end def


# 이미지파일명 추출
# description : 전체URL에서 파일명만 추출
def GET_FILENAME_FROM_URL(src) :
	cnt = src.rfind("/") + 1				# 파일명 시작위치
	fullfilename = src[cnt:]				# 전체파일명

	return fullfilename
#end def


# 파일명만 추출
def GET_FILENAME(src) :
	filename = src.split(".")[0]		# 파일명

	return filename
#end def


# 확장자만 추출
def GET_EXT(src) :
	ext = src.split(".")[1]		# 확장자

	return ext
#end def


# json default : datetime
def JSON_DEFAULT(pa) :
	if isinstance(pa, datetime) :
		return pa.__str__()
	#end if
#end def


#####################################################################################
### project function
#####################################################################################
# None 여부를 boolean으로 변환
def IS_BOOL(pa) :
	rtn = False

	if str(pa) != "None" :
		rtn = True
	#end if

	return rtn
#end def


# html에서 엘리먼트 유무에 따라 True / False 반환
def IS_ELEMENT(pa) :
	rtn = False

	if str(pa) != "None" :
		rtn = True
	#end if

	return rtn
#end def


# category dictionary 생성 : 'cate_nm' : 'cate_cd'
def GET_CATEGORY_DICT() :
	rtn = {}

	sql = cMysql.cMysql(con.DB_INFO)
	sql.db_conn()

	param = []
	qry = """
		SELECT CATE_CD, CATE_NM, DEPTH
		FROM TB_CATEGORY
		WHERE USE_YN = 'Y'
		ORDER BY CATE_CD
	"""
	result = sql.exec('list', qry, param)
	rs = result['data']

	sql.close()

	for ds in rs :
		key = ds['CATE_NM'] +'_'+ str(ds['DEPTH'])
		rtn[key] = ds['CATE_CD']
	#end for

	return rtn
#end def


# 기존에 존재하는 product_cd 확인
def IS_PRODUCT(p_cd) :
	rtn = ''

	sql = cMysql.cMysql(con.DB_INFO)
	sql.db_conn()

	param = []
	qry = """
		SELECT COUNT(PRODUCT_SEQ) AS CNT
		FROM TB_PRODUCT
		WHERE PRODUCT_CD = %s
	"""
	param.append(p_cd)
	result = sql.exec('data', qry, param)
	ds = result['data']
	rtn = ds['CNT']

	sql.close()

	return rtn
#end def