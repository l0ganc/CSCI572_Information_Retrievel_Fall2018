package edu.scf.usc;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.Reader;

import org.jsoup.Jsoup;

public class GenerateBigTxt {
    private static final String path = "/Users/logan/Downloads/IR-Fall-2018/mercurynews/mercurynews/";
    public static void main(String [] args) throws IOException {
        File dir = new File(path);
        File [] file_list = dir.listFiles();
        if(file_list != null) {
            new File("/Users/logan/Downloads/big.txt").delete();
            for(File file:file_list) {
                FileReader reader = new FileReader(file.getAbsolutePath());
                String result = extractText(reader);
                writeToFile(result);
            }
        }else {
            System.out.println("Not a valid directory!");
        }
        System.out.println("Finished!");
    }

    public static String extractText(Reader reader) throws IOException {
        StringBuilder sb = new StringBuilder();
        BufferedReader br = new BufferedReader(reader);
        String line;
        while ( (line=br.readLine()) != null) {
            sb.append(line);
        }
        String textOnly = Jsoup.parse(sb.toString()).text();
        return textOnly;
    }

    private static void writeToFile(String content) {
        BufferedWriter bw = null;
        FileWriter fw = null;

        try {
            fw = new FileWriter("/Users/logan/Downloads/big.txt", true);
            bw = new BufferedWriter(fw);
            bw.write(content);
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            try {
                if (bw != null) bw.close();
                if (fw != null) fw.close();
            } catch (IOException ex) {
                ex.printStackTrace();
            }
        }
    }
}
