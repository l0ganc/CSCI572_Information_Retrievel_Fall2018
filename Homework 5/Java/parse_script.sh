i=1
for file in /Users/logan/Downloads/IR-Fall-2018/mercurynews/mercurynews/*.*
do
	echo "process file no.:$i"
	java -jar tika-app-1.19.1.jar --text $file >> parsed.txt
	i=$((i+1))
done
