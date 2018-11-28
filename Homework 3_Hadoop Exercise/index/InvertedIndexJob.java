import java.io.IOException;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map.Entry;
import java.util.StringTokenizer;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.mapreduce.*;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.input.FileSplit;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;


public class InvertedIndexJob {


    static class InvertIndexMapper extends Mapper<LongWritable, Text, Text, Text>{
        private Text word = new Text();
        @Override
        public void map(LongWritable key, Text value, Context context) throws IOException, InterruptedException{
            FileSplit fileSplit = (FileSplit)context.getInputSplit();
            String filename = fileSplit.getPath().getName();
            try{
                String line = value.toString();
                StringTokenizer tokenizer = new StringTokenizer(line);
                Text docId = new Text();
                docId.set(tokenizer.nextToken());

                String lineClean = line.replaceAll("\\P{L}", " ").toLowerCase();
                StringTokenizer tokenizerClean = new StringTokenizer(lineClean);
                while(tokenizerClean.hasMoreTokens()){
                    word.set(tokenizerClean.nextToken());
                    context.write(word, docId);
                }
            }catch(Exception e){
                e.printStackTrace();
                throw new InterruptedException("Can't process file: " + filename);
            }
        }

    }


    static class InvertIndexReducer extends Reducer<Text, Text, Text, Text>{
        @Override
        protected void reduce(Text word, Iterable<Text> docIds,
                              Context context) throws IOException, InterruptedException {

            HashMap<String, Integer> hashDocIdCount = new HashMap<>();
            for(Text docId: docIds){
                String docIdStr = docId.toString();
                int count = hashDocIdCount.containsKey(docIdStr)? (hashDocIdCount.get(docIdStr)+1) : 1;

                hashDocIdCount.put(docIdStr, count);
            }

            StringBuffer listStr = new StringBuffer();
            Iterator<Entry<String, Integer>> iterator = hashDocIdCount.entrySet().iterator();
            while(iterator.hasNext()){
                Entry<String,Integer> entry = iterator.next();
                if(listStr.length() > 0){
                    listStr.append("\t");
                }

                listStr.append(entry.getKey() + ":" + entry.getValue());
            }

            Text documentList = new Text(listStr.toString());
            context.write(word, documentList);
        }
    }


    public static void main(String []args) throws
            IOException, ClassNotFoundException, InterruptedException{
        if(args.length != 2){
            System.err.println("Usage: InvertIndex <input_path> <output_path>");
            System.exit(-1);
        }

        Job job = new Job();
        job.setJarByClass(InvertedIndexJob.class);
        job.setJobName("InvertIndex");
        FileInputFormat.addInputPath(job, new Path(args[0]));
        FileOutputFormat.setOutputPath(job, new Path(args[1]));

        job.setMapperClass(InvertIndexMapper.class);
        job.setReducerClass(InvertIndexReducer.class);

        job.setOutputKeyClass(Text.class);
        job.setOutputValueClass(Text.class);
        job.waitForCompletion(true);
    }
}


